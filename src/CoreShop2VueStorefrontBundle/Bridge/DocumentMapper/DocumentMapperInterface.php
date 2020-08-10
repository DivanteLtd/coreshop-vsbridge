<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

interface DocumentMapperInterface
{
    public function mapToDocument($object, ?string $language = null);
}
