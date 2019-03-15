<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image\Thumbnail\Config;
use Pimcore\Model\Asset\Image\Thumbnail\Processor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ImageController extends AbstractController
{
    /**
     * @Route(
     *     "/img/{width}/{height}/{operation}/{imgPath}",
     *     name="get_image",
     *     requirements={"imgPath"=".+"}
     * )
     *
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Asset\Image\Thumbnail|string
     */
    public function generateImage(Request $request)
    {
        $relativeUrl = $request->get('imgPath');

        $asset = Asset::getByPath(sprintf('%s%s', DIRECTORY_SEPARATOR, $relativeUrl));
        if ($asset) {
            $config = Config::getByAutoDetect([
                'width' => $request->get('width'),
                'height' => $request->get('height')
            ]);

            $thumbnail = Processor::process($asset, $config);

            $pathToThumbnail = sprintf("%s%s%s", PIMCORE_TEMPORARY_DIRECTORY, '/image-thumbnails', $thumbnail);

            if (file_exists($pathToThumbnail)) {
                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basepath($relativeUrl));
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', $asset->getEXIFData()['MimeType']);
                $response->setContent(file_get_contents($pathToThumbnail));

                return $response;
            }
        }

        return new Response();
    }
}
