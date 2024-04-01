<?php

namespace App\Services;

use App\Services\Interfaces\UserCatalogueServiceInterface;
use App\Repositories\Interfaces\UserCatalogueRepositoryInterface as UserCatalogueRepository;
use App\Repositories\Interfaces\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class UserCatalogueService
 * @package App\Services
 */
class UserCatalogueService implements UserCatalogueServiceInterface
{
    protected $userRepository;
    protected $userCatalogueRepository;

    public function __construct(
        UserRepository $userReponsitory,
        UserCatalogueRepository $userCatalogueReponsitory
    ) {
        $this->userRepository = $userReponsitory;
        $this->userCatalogueRepository = $userCatalogueReponsitory;
    }

    public function paginate($request) {
        $condition['keyword'] = $request->input('keyword');
        $condition['publish'] = $request->input('publish');
        $perPage = $request->integer('perpage');
        $userCatalogue = $this->userCatalogueRepository->pagination(
            $this->select(),
            $condition,
            [],
            ['path' => 'user_catalogue/index'],
            $perPage, ['users']
        );

        return $userCatalogue;
    }

    public function create(Request $request) {
        DB::beginTransaction();
        try {
            $payload = $request->except('_token', 'send');
            $userCatalogue = $this->userCatalogueRepository->create($payload);
            DB::commit();
            return true;

        }catch(\Exception $e) {
            DB::rollBack();

            echo $e->getMessage();die();
            return false;
        }
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();
        try {
            $payload = $request->except('_token', 'send');
            $user = $this->userCatalogueRepository->update($id, $payload);
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            
            echo $e->getMessage();die();
            return false;
        }
    }

    public function updateStatus($post = []) {
        DB::beginTransaction();
        try {
            $payload[$post['field']] = (($post['value'] == 1) ? 2:1);
            $userCatalogue = $this->userCatalogueRepository->update($post['modelId'], $payload);
            $this->changeUserStatus($post, $payload[$post['field']]);
            
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            
            echo $e->getMessage();die();
            return false;
        }
    }

    public function updateStatusAll($post) {
        DB::beginTransaction();
        try {
            
            $payload[$post['field']]  = $post['value'];
            $userCatalogue = $this->userCatalogueRepository->updateByWhereIn('id', $post['id'], $payload);
            $this->changeUserStatus($post, $payload[$post['field']]);
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            
            echo $e->getMessage();die();
            return false;
        }
    }

    public function changeUserStatus($post, $value) {
        DB::beginTransaction();
        try {
            $array = [];
            if(isset($post['modelId'])) {
                $array[] = $post['modelId'];
            }else{
                $array = $post['id'];
            }
            $payload[$post['field']] = $value;

            $this->userRepository->updateByWhereIn('user_catalogue_id', $array, $payload);
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            
            echo $e->getMessage();die();
            return false;
        }
    }

    public function setPermission(Request $request) {
        DB::beginTransaction();
        try {
            
            $permissions = $request->input('permission');
            if(count($permissions)) {
                foreach($permissions as $key => $val) {
                    $userCatalogue = $this->userCatalogueRepository->findById($key);
                    $userCatalogue->permissions()->detach();
                    $userCatalogue->permissions()->sync($val);
                }
            }
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            
            echo $e->getMessage();die();
            return false;
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $user = $this->userCatalogueRepository->forceDelete($id);
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();die();
            return false;
        }
    }

    private function select() {
        return [
            '*'
        ];
    }
}