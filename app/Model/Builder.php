<?php


namespace App\Model;


/**
 * Interface Builder
 * @package App\Model
 */
interface Builder
{
    public function prepare(): self;

    /**
     * @return array
     */
    public function build();
}
