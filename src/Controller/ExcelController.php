<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Excel;
use App\Form\ExcelType;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Repository\PositionRepository;
use App\Repository\ExcelRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/excel')]
class ExcelController extends AbstractController
{
    #[Route('/', name: 'excel_index', methods: ['GET'])]
    public function index(ExcelRepository $excelRepository): Response
    {
        return $this->render('excel/index.html.twig', [
            'excels' => $excelRepository->findAll(),
        ]);
    }
    // load data
    #[Route('/new', name: 'excel_new', methods: ['GET', 'POST'])]
    public function new(DepartmentRepository $departmentRepository,PositionRepository $positionRepository,UserRepository $userRepository, Request $request, SluggerInterface $slugger): Response
    {
        //load positions
        //step1 - prepare
        $excel = new Excel();
        $form = $this->createForm(ExcelType::class, $excel);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            //step2 - save file for reading
            $excelFile = $form->get('excel')->getData();
            if ($excelFile) {
                $originalFilename = pathinfo($excelFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$excelFile->guessExtension();
                try {
                    $excelFile->move(
                        $this->getParameter('excel_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    dd($e);
                }
                $excel->setName($newFilename);
                //step3 - load data
                $spreadsheet = IOFactory::load($this->getParameter('excel_directory') . $newFilename);  
                $row = $spreadsheet->getActiveSheet()->removeRow(1); 
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); 
                
                //step 4.1 - save data
                $entityManager = $this->getDoctrine()->getManager(); 
                foreach ($sheetData as $key => $Row) {
                    if($Row['A'] && $Row['B'] && $Row['C'] ){    
                    $firstname = $Row['A']; 
                    $lastname = $Row['B'];
                    $idnumber= $Row['C']; 
                    //check if user exists
                    $user_existant =$userRepository->findOneBy(array('idnumber' => $idnumber)); 
                    //create new one
                    //for flash messages:
                        $position =$positionRepository->findOneBy(array('name' => $Row['D']));
                        if(!$position){
                        $this->addFlash('info', "missing position for user with an ID: $idnumber !");
                        }
                        $department =$departmentRepository->findOneBy(array('name' => $Row['E']));
                        if(!$department){
                        $this->addFlash('info', "missing department for user with an ID: $idnumber !");
                        }
                    if (!$user_existant) {
                        $user = new User(); 
                        $user->setFirstName($firstname);     
                        $user->setLastname($lastname);     
                        $user->setIdnumber($idnumber);     
                        $user->setPositionId($position);     
                        $user->setDepartmentId($department);
                        $entityManager->persist($user); 
                        $entityManager->flush();  
                        $this->addFlash('success', "created: $idnumber");    
                    } else {
                     //update old
                        $user = $user_existant;
                        $user->setFirstName($firstname);     
                        $user->setLastname($lastname);     
                        $user->setPositionId($positionRepository->findOneBy(array('name' => $Row['D'])));     
                        $user->setDepartmentId($departmentRepository->findOneBy(array('name' => $Row['E'])));
                        $entityManager->flush();
                        $this->addFlash('update', "updated: $idnumber");
                    }}
                     else {
                         //case row is not cosindered to create/update
                        if(!$Row['A']){
                            $this->addFlash('danger', "missing firstname on the row no:". $key+1);
                        }
                        if(!$Row['B']){
                            $this->addFlash('danger', "missing lastname on the row no:". $key+1);
                        }
                        if(!$Row['C']){
                            $this->addFlash('danger', "missing idnumber on the row no:". $key+1);
                        }
                
                    
                }}
                
            
            }
        }

        return $this->render('excel/new.html.twig', [
            'excel' => $excel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'excel_show', methods: ['GET'])]
    public function show(Excel $excel): Response
    {
        return $this->render('excel/show.html.twig', [
            'excel' => $excel,
        ]);
    }

    #[Route('/{id}/edit', name: 'excel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Excel $excel): Response
    {
        $form = $this->createForm(ExcelType::class, $excel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('excel_index');
        }

        return $this->render('excel/edit.html.twig', [
            'excel' => $excel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'excel_delete', methods: ['DELETE'])]
    public function delete(Request $request, Excel $excel): Response
    {
        if ($this->isCsrfTokenValid('delete'.$excel->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($excel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('excel_index');
    }
}