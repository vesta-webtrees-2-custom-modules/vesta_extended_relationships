<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

class IndividualsWithPatriarchs {

    protected $originalCollection;
    protected $patriarchs;

    public function getOriginalCollection() {
        return $this->originalCollection;
    }

    public function getPatriarchs() {
        return $this->patriarchs;
    }

    public function __construct(
        $originalCollection,
        $patriarchs) {

        $this->originalCollection = $originalCollection;
        $this->patriarchs = $patriarchs;
    }

}
