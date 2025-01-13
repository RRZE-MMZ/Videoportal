<?php

namespace App\Services;

use App\Http\Clients\OpenSearchClient;
use App\Models\Setting;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSearch\ClientBuilder;

class OpenSearchService
{
    private string $type;

    private Collection $response;

    private Setting $openSearchSettings;

    public function __construct(private ClientBuilder $clientBuilder, private OpenSearchClient $client)
    {
        $this->type = '';
        $this->response = collect([]);
        $this->openSearchSettings = Setting::openSearch();
    }

    /*
     * returns OpenSearch cluster health
     */
    public function getHealth(): Collection
    {
        $this->response = collect([
            'releaseId' => [
                'version' => [
                    'number' => 'OpenSearch server not available',
                    'build_type' => 'unknown',
                ],
            ],
            'status' => 'failed',
        ]);
        try {
            $response = $this->client->get('/');

            if (! empty(json_encode((string) $response->getBody(), true))) {
                $this->response->put('releaseId', json_decode((string) $response->getBody(), true))
                    ->put('status', 'pass');
            }
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    public function createIndex($model): Collection
    {
        $this->type = $model->getTable();
        try {
            $params = [
                'index' => $this->openSearchSettings->data['prefix'].$this->type,
                'id' => "{$this->type}_$model->id",
                'body' => $model->toJson(),
            ];
            $this->response = collect($this->clientBuilder->build()->index($params));
        } catch (Exception $exception) {
            Log::error($exception->getMessage().'|'.$this->type.'ID:'.$model->id);
        }

        return $this->response;
    }

    public function updateIndex($model): Collection
    {
        $this->type = $model->getTable();

        try {
            $params = [
                'index' => $this->openSearchSettings->data['prefix'].$this->type,
                'id' => "{$this->type}_$model->id",
                'body' => [
                    'doc' => $model,
                    'doc_as_upsert' => true,
                ],
            ];

            $this->response = collect($this->clientBuilder->build()->update($params));
        } catch (Exception $exception) {
            // avoid error messages if it is running on console command
            if (! App::runningInConsole()) {
                Log::error($exception->getMessage());
            }
        }

        return $this->response;
    }

    public function deleteIndex(Model $model): Collection
    {
        $this->type = $model->getTable();

        try {
            $params = [
                'index' => $this->openSearchSettings->data['prefix'].$this->type,
                'id' => $this->type.'_'.$model->id,
            ];

            $this->response = collect($this->clientBuilder->build()->delete($params));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    public function fetchDocument($index, $id): Collection
    {
        try {
            $params = [
                'index' => $index,
                'id' => $id,
            ];

            $this->response = collect($this->clientBuilder->build()->getSource($params));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    /**
     * @throws GuzzleException
     */
    public function deleteIndexes(string $model = ''): Collection
    {
        try {
            $this->response =
                collect($this->client->delete($this->openSearchSettings->data['prefix'].Str::lower($model)));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    public function searchIndexes(
        string $index,
        string $searchTerm,
        array $filters = [],
        int $page = 1,
        int $pageSize = 100
    ): Collection {
        $from = ($page - 1) * $pageSize;
        $termFilters = [];
        if (! empty($filters)) {
            foreach ($filters as $key => $value) {
                $termFilters[] = ['term' => [$key => $value]];
            }
        }

        $isPhrase = Str::contains($searchTerm, ' ');

        $query = ($isPhrase) ?
            [
                //                'multi_match' => [
                //                    'must' => [
                //                        'multi_match' => [
                //                            'query' => "{$searchTerm}",
                //                            //                            'fuzziness' => 1,
                //                            //                            'slop' => 1,
                //                            //                            'max_expansions' => 1,
                //                            //                            'prefix_length' => 1,
                //                        ],
                //                    ],
                //                    'filter' => ! empty($termFilters) ? $termFilters : [],
                //                ],
                'multi_match' => [
                    'query' => "{$searchTerm}",
                    'type' => 'phrase',
                    //                    'filter' => ! empty($termFilters) ? $termFilters : [],
                ],
            ]
            : [
                'bool' => [
                    'must' => [
                        'multi_match' => [
                            'query' => "{$searchTerm}",
                            'fuzziness' => 1,
                            'slop' => 1,
                            'max_expansions' => 1,
                            'prefix_length' => 1,
                        ],
                    ],
                    'filter' => ! empty($termFilters) ? $termFilters : [],
                ],
            ];
        try {
            $params = [
                'index' => $index,
                'body' => [
                    'sort' => [
                        ['updated_at' => ['order' => 'desc']],
                    ],
                    'from' => $from,
                    'size' => $pageSize,
                    'query' => $query,
                    '_source' => ['*'],
                    'highlight' => [
                        'pre_tags' => '<mark>',
                        'post_tags' => '<\/mark>',
                        'fields' => [],
                    ],
                ],
            ];
            $this->response = collect($this->clientBuilder->build()->search($params));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }
}
