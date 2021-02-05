<?php

namespace App\Controller;

use App\Entity\Department;
use App\Entity\Position;
use App\Entity\User;
use App\Entity\Excel;
use App\Form\ExcelType;
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
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        //step1 - prepare
        $excel = new Excel();
        $form = $this->createForm(ExcelType::class, $excel);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $excelFile */
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
                 $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line 
                 $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); 
                dump($sheetData);
                //step4 - save data
                 $entityManager = $this->getDoctrine()->getManager(); 
                foreach ($sheetData as $Row) {
                    $firstname = $Row['A']; 
                    $lastname = $Row['B'];
                    $idnumber= $Row['C']; 
                    $position_id= $Row['D']; 
                    $department_id= $Row['E']; 

                    $user_existant = $entityManager->getRepository(User::class)->findOneBy(array('idnumber' => $idnumber)); 
                    if (!$user_existant) {
                    $user = new User(); 
                    $user->setFirstName($firstname);     
                    $user->setLastname($lastname);     
                    $user->setIdnumber($idnumber);     
                    $user->setPositionId($position_id);     
                    $user->setDepartmentId($department_id);
                    $entityManager->persist($user); 
                    $entityManager->flush();      
                     }
                }
                
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