<?php

namespace Vortexgin\MorphAnalyzerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DefaultController extends Controller
{

    /**
     * @ApiDoc(
     *      section="Tools",
     *      resource="Morphological Analyzer",
     *      description="Analyze word",
     *      parameters={
     *          {"name"="word", "dataType"="string", "required"=true, "description"="word"},
     *      },
     *      statusCodes={
     *          200="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function analyzeAction(Request $request)
    {
        try{
            $post = $request->request->all();

            if(!array_key_exists('word', $post) || empty($post['word'])){
                return new JsonResponse(array(
                    'message' => 'Please insert word',
                    'success' => false,
                    'timestamp' => new \DateTime()
                ), 400);
            }

            /* @var $morphManager \Vortexgin\MorphAnalyzerBundle\Manager\MorphManager */
            $morphManager = $this->container->get('vortexgin.morph.manager');

            $return = $morphManager->analyze($post['word']);

            return new JsonResponse($return, 200);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return new JsonResponse(array(
                'message' => 'Analyze failed, Please try again later',
                'success' => false,
                'timestamp' => new \DateTime()
            ), 412);
        }
    }
}
