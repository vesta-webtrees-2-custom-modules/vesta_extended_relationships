<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

class SurnameWithPatriarch {

    protected $actualSurname;
    protected $patriarchXref;
    protected $same;
    protected $count;
    protected $helpLink;

    public function getActualSurname(): string {
        return $this->actualSurname;
    }

    public function getPatriarchXref(): string {
        return $this->patriarchXref;
    }

    public function isSame(): bool {
        return $this->same && ($this->count == 1);
    }

    public function getCount(): int {
        return $this->count;
    }

    public function increment() {
        $this->count++;
    }

    public function getHelpLink() {
        return $this->helpLink;
    }
    
    public function __construct(
        string $actualSurname,
        string $patriarchXref,
        bool $same,
        string $helpLink) {

        $this->actualSurname = $actualSurname;
        $this->patriarchXref = $patriarchXref;
        $this->same = $same;
        $this->count = 1;
        $this->helpLink = $helpLink;
    }

}
