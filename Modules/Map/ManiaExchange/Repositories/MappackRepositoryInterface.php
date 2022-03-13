<?php


namespace Modules\Map\ManiaExchange\Repositories;


interface MappackRepositoryInterface
{
    public function findById(int $id);

    public function getTracks(int $id);
}
