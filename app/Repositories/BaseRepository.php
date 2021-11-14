<?php
namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'])
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $with
     * @return Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null, $with = [])
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
                if (in_array($key, $this->getFieldSearchLikeable())){
                   $query->where($key, 'LIKE', "%{$value}%");
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        if (count($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @param array $with
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'], $with = [])
    {
        $query = $this->allQuery($search, $skip, $limit, $with);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }


    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model
     */
    public function findOrFail($id)
    {
        $query = $this->model->newQuery();

        return $query->findOrFail($id);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return Builder|Builder[]|Collection|Model
     */
    public function update($input, $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }

    /**
     * @param $input
     * @param array $withs
     * @return mixed
     */
    public function where($input, $withs = [])
    {
        return $this->model()::with($withs)->where($input);
    }

    /**
     * @param array $rel
     * @return mixed
     */
    public function with(array $rel)
    {
        return $this->model()::with($rel)->get();
    }

    /**
     * @param $column
     * @param $arr
     * @return mixed
     */
    public function whereIn($column, $arr)
    {
        return $this->model()::whereIn($column, $arr)->get();
    }

    /**
     * @param $column
     * @param $arr
     * @return mixed
     */
    public function whereNotIn($column, $arr)
    {
        return $this->model()::whereNotIn($column, $arr)->get();
    }

    /**
     * @return mixed
     */
    public function totalRecord()
    {
        return $this->model->count();
    }

    /**
     * @var array
     */
    private $exists = [];

    /**
     * @param $params
     * @param array $extra
     * @return array
     */
    public function genPaginate($params, $extra = [])
    {
        $page = isset($params['page']) && (int)$params['page'] > 0 ? $params['page'] : 1;
        $perPage = isset($params['per_page']) && (int)$params['per_page'] > 0 ? $params['per_page'] : DEFAULT_PAGE;

        $offset = ($page - 1) * $perPage;

        $totalItems = $this->totalRecord();
        $items = $this->allQuery($params['search'] ?? [], $offset, $perPage);
        if(empty($extra['orderBys'])) $items = $items->orderBy('id', 'desc')->get();
        else {
            foreach ($extra['orderBys'] as $key => $item){
                $items->orderBy($key, $item);
            }
            $items = $items->get();
        }
        $totalPage = ceil($totalItems/$perPage);

        return [
            'current_page' => (int)$page,
            'next_page' => $page < $totalPage ? $page + 1 : null,
            'prev_page' => (int)$page > 1 ? $page - 1 : null,
            'per_page' => (int)$perPage,
            'total_page' => ceil($totalItems/$perPage),
            'items' => $items
        ];
    }

    public function filterData($array)
    {
        return $array->request->all();
    }

    /**
     * @param $columns
     * @return array
     */
    public function selectColumns($columns)
    {
        return $this->model->newQuery()->select($columns)->get()->toArray();
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return $this->model->first();
    }

    /**
     * @return mixed
     */
    public function last()
    {
        return $this->model->orderBy('id', 'desc')->first();
    }
}
