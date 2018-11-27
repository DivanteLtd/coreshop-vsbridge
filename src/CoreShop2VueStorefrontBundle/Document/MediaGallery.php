<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class MediaGallery
{
    /** @ES\Property(type="string") */
    public $image;

    /** @ES\Property(type="integer") */
    public $pos;

    /** @ES\Property(type="string") */
    public $typ;

    /** @ES\Property(type="string") */
    public $lab;

    public function __construct(string $imagePath = null, int $position = 1, string $type = 'image', string $lab = null)
    {
        $this->image = $imagePath;
        $this->pos = $position;
        $this->typ = $type;
        $this->lab = $lab;
    }
}
