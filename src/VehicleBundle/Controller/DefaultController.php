<?php

namespace VehicleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use VehicleBundle\Entity\Make;
use VehicleBundle\Entity\Model;
use VehicleBundle\Repository\MakeRepository;

class DefaultController extends Controller
{
    /**
     * @Route("/admin")
     */
    public function indexAction()
    {

        return $this->render('VehicleBundle:Default:index.html.twig');
    }


    /**
     * @Route("/admin/get/makes")
     */
    public function makeAction()
    {

        $makeRepository = $this->getDoctrine()->getRepository('VehicleBundle:Make');
        $allMakes = $makeRepository->findAll();
        //var_dump($allMakes);
        $json_array = array();
        foreach($allMakes as $make) {
            $json_array[] = array("id" => $make->getId(),"name" => $make->getName());
        }
        echo json_encode($json_array);
        die();
    }

    /**
     * @Route("/admin/get/model/{makeId}")
     */
    public function modelAction($makeId,Request $request)
    {
        $modelRepository = $this->getDoctrine()->getRepository('VehicleBundle:Model');
        $allModels = $modelRepository->findBy(array('makeId' => $makeId));
        $json_array = array();
        foreach($allModels as $model) {
            $json_array[] = array("id" => $model->getId(),"name" => $model->getName());
        }
        echo json_encode($json_array);
        die();
    }

    /**
     * @Route("/admin/fetchdata")
     */
    public function fetchAction( Request $request)
    {
        // Provide updates to the user regularly
        header('Content-Type: text/event-stream');
        // recommended to prevent caching of event data.
        header('Cache-Control: no-cache');

        $em = $this->getDoctrine()->getManager();
        $em->createQuery('DELETE FROM VehicleBundle\Entity\Make')->execute();
        $em->createQuery('DELETE FROM VehicleBundle\Entity\Model')->execute();

        $makeUrl = $this->getEdmundsAPIUrl("makes");
        $time_start = microtime(true);
        ini_set('max_execution_time', 0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $makeUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        // If using JSON...
        $makes = json_decode($response)->makes;
        $allMakes = array();
        $i = 1;
        $totalMakes = count($makes);

        foreach($makes as $make){
            $this->sendUpdate("Updating Make and Model Info for ".$make->name, ($i++/$totalMakes)*100);

            $makeObj = new Make();
            $makeObj->setName($make->name);
            $makeObj->setMakeNiceName($make->niceName);

           // $makeRepository = $em->getRepository('VehicleBundle:Make');
          //  $makeRepository->insertMakeObj($makeObj);

            $em->persist($makeObj);
            $em->flush();

            $modelUrl = $make->niceName."/models";
            $modelUrl = $this->getEdmundsAPIUrl($modelUrl);
            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_URL, $modelUrl);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
            $response2 = curl_exec($ch2);
            $models = json_decode($response2)->models;
            foreach($models as $model){
                $modelObj = new Model();
                $modelObj->setName($model->name);
                $modelObj->setMakeId($makeObj->getId());
                $em->persist($modelObj);
                $em->flush();
            }

        }


        ini_set('max_execution_time', 30);

        $time_end = microtime(true);
        $time = $time_end - $time_start;
        die();
    }

    public function getEdmundsAPIUrl($method){
        $api_key = $this->container->getParameter('api_key');
        $api_endpoint = $this->container->getParameter('api_endpoint');
        $view = $this->container->getParameter('view');
        $fmt = $this->container->getParameter('fmt');
        return $api_endpoint.$method."?view=".$view."&fmt=".$fmt."&api_key=".$api_key;
    }

    public function sendUpdate($message, $progress){
        $d = array('message' => $message , 'progress' => round($progress));
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        ob_flush();
        flush();
    }
}
