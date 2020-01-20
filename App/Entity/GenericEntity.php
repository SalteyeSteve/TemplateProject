<?php

namespace App\Entity;


class GenericEntity
{
    private $id;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
