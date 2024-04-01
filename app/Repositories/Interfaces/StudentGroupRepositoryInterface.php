<?php

namespace App\Repositories\Interfaces;

/**
 * Interface StudentGroupRepositoryInterface
 * @package App\Services\Interfaces
 */
interface StudentGroupRepositoryInterface
{
    public function all();

    public function pagination(
        array $column = ['*'],
        array $condition = [],
        array $join = [],
        array $extend = [],
        int $perPage = 20
    );

    public function create(array $payload = []);

    public function findById(int $id);

    public function findStudentIdsByLecturerId(int $lecturerId): array;

    public function getAllLecturerIds();

    public function getAllStudentIds();

    public function update(int $id = 0, array $payload = []);

    public function updateByWhereIn(
        string $whereInFeild = '',
        array $whereIn = [], 
        array $payload = []);

    public function delete(int $id);

    public function forceDelete(int $id);
}
