<?php

namespace Supermetrics\SDK;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SocialAnalytics
 * @package Supermetrics\SDK
 */
class SocialAnalytics
{
    /**
     * @var string
     */
    protected string $slToken;

    /**
     * @var \Supermetrics\SDK\Authenticate|null
     */
    private ?Authenticate $authInstance = null;

    /**
     * @var string
     */
    private string $postsURL = "https://api.supermetrics.com/assignment/posts";


    /**
     * @var array
     */
    public array $posts = [];

    /**
     * SocialAnalytics constructor.
     * @param string $slToken
     */
    public function __construct(string $slToken)
    {
        $this->slToken = $slToken;
    }


    /**
     * @param \Supermetrics\SDK\Authenticate $authenticate
     */
    public function autoGenerateSLTokenOnExpire(Authenticate $authenticate)
    {
        $this->authInstance = $authenticate;
    }


    /**
     * This method utilizes pool request concept to get all the post from all the pages, as its a rest API,
     * Order of completion of request is not important, we will store the given result in an array which can be used
     * for different purposes not limited to post analytics only
     */
    public function posts(): static
    {
        $client = new Client();
        /**
         * Generating request for each page asynchronously to put in request pool
         * @return \Generator
         */
        $requests = function () {
            for ($page = 1; $page <= 10; $page++) {
                yield new Request(
                    'GET',
                    (new Uri($this->postsURL))->withQuery(http_build_query([
                        'sl_token' => $this->slToken,
                        'page'     => $page
                    ]))
                );
            }
        };
        /**
         * Initiating Pool request
         */
        $requestPool = new Pool($client, $requests(), [
            //request concurrency
            'concurrency' => 10,
            //success callback
            'fulfilled'   => function (ResponseInterface $response) {
                if ($response->getStatusCode() == 200) {
                    $responseContent = json_decode((string)$response->getBody());
                    $this->posts = array_merge($this->posts, $responseContent->data?->posts);
                }
            },
            // rejected callback
            'rejected'    => function (RequestException $exception) {
                // In case of rejected do nothing, just dumping exception to debug
                var_dump($exception->getMessage());
            }
        ]);

        $promise = $requestPool->promise();
        $promise->wait();
        return $this;
    }


    #[ArrayShape([
        'avg_char_length_post_per_month' => "array",
        'longest_post_per_month'         => "array",
        'total_post_per_week'            => "array",
        'avg_post_per_user_per_month'    => "array"
    ])]
    public function stats(): array
    {
        $postStats = [
            'avg_char_length_post_per_month' => [],
            'longest_post_per_month'         => [],
            'total_post_per_week'            => [],
            'avg_post_per_user_per_month'    => []
        ];

        foreach ($this->posts as $post) {
            $date = Carbon::parse($post->created_time);
            $postLength = strlen($post->message);

            /**
             * avg_char_length_post_per_month
             * We will first collect the length of the post for that month and then in
             * the end we will calculate the average to finally struct our stats
             */
            if (isset($postStats['avg_char_length_post_per_month'][$date->monthName])) {
                $postStats['avg_char_length_post_per_month'][$date->monthName][] = $postLength;
            } else {
                $postStats['avg_char_length_post_per_month'][$date->monthName] = [$postLength];
            }

            /**
             * longest_post_per_month
             * Here we have to do just comparison so no post processing  required for this stats
             * if the length of the current post is greater than existing once rthen the longest post wil be replaced
             * else just leave that
             */
            if (!isset($postStats['longest_post_per_month'][$date->monthName])) {
                $postStats['longest_post_per_month'][$date->monthName] = $post->message;
            } else {
                $longestPostForTheMonth = $postStats['longest_post_per_month'][$date->monthName];
                $postStats['longest_post_per_month'][$date->monthName] = ($postLength > strlen($longestPostForTheMonth))
                    ? $post->message : $longestPostForTheMonth;
            }

            /**
             * total_post_per_week
             * Again here we just need to increase up the number of post for that week number if any post found
             * Otherwise do nothing, again no further processing required
             * This could be done using ternary operator but length of line will increase, so just utilising if else for readability
             */
            $weekNumberInString = "week_{$date->weekOfYear}";
            if (isset($postStats['total_post_per_week'][$weekNumberInString])) {
                $postStats['total_post_per_week'][$weekNumberInString] += 1;
            } else {
                $postStats['total_post_per_week'][$weekNumberInString] = 1;
            }

            /**
             * avg_post_per_user_per_month
             * Average Post per user Per Month, again this includes average so it will require a post processing
             * as we do not know the exact number of month while iterating, hence we will do post process on this as well
             */
            if (isset($postStats['avg_post_per_user_per_month'][$post->from_id])) {
                $postStats['avg_post_per_user_per_month'][$post->from_id][$date->monthName] = (isset($postStats['avg_post_per_user_per_month'][$post->from_id][$date->monthName]))
                    ? $postStats['avg_post_per_user_per_month'][$post->from_id][$date->monthName] + 1
                    : 1;
            } else {
                $postStats['avg_post_per_user_per_month'][$post->from_id][$date->monthName] = 1;
            }
        }
        /**
         * Doing post processing of data for final stats,
         * Here converting both averages in int as those becomes smallest unit
         * example no one can type 1.5 character or no on can make 1.5 post, that will be always a whole number
         */
        foreach ($postStats['avg_char_length_post_per_month'] as $month => $postLengths) {
            $postStats['avg_char_length_post_per_month'][$month] = (int)(array_sum($postLengths) / count($postLengths));
        }

        foreach ($postStats['avg_post_per_user_per_month'] as $user => $postPerMonth) {
            $postStats['avg_post_per_user_per_month'][$user] = (int)(array_sum(array_values($postPerMonth)) / count($postPerMonth));
        }

        return $postStats;
    }
}