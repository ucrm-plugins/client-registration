<?php
declare(strict_types=1);

namespace App\Controllers;

use Dotenv\Dotenv;
use Monolog\Logger;
use MVQN\HTTP\Slim\Routes\BuiltInRoute;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use UCRM\Common\Log;
use UCRM\Common\Plugin;

/**
 * Class ApiController
 *
 * An API controller.
 *
 * @package App\Controllers
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class ApiController extends BuiltInRoute
{
    /**
     * ApiController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     */
    public function __construct(App $app)
    {

        $this->route = $app->group("/api", function() use ($app) {

            // Get a local reference to the Slim Application's DI Container.
            $container = $app->getContainer();

            // Include the LogsController for querying the Plugin's log files.
            new API\LogsController($app);

            new API\PgsqlController($app);

            new API\PermissionsController($app);

            //
            // NOTE: Include any additional common API Controllers here...
            //



            $app->get("/plugin",

                function (Request $request, Response $response, array $args) use ($container)
                {
                    $data = [
                        "mode" => Plugin::mode(),
                        // NOTE: Add additional Plugin metadata here...
                    ];

                    return $response->withJson($data);
                }
            );



            // Handle the root "/api[/]" functionality here...
            $app->get("[/]",

                function (Request $request, Response $response, array $args) use ($container)
                {


                    // Return the API information as JSON!
                    return $response->withJson(
                        [
                            "version" => "1.0",
                            "endpoints" => [
                                "/api/logs" => [
                                    "name" => "Logs",
                                    "description" => "An endpoint for querying the Plugin's log files."
                                ]
                            ]
                        ],
                        200,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }
            );

            $app->get("*",

                function (Request $request, Response $response, array $args) use ($container)
                {

                    // Return the API information as JSON!
                    return $response->withJson(
                        [
                            "version" => "1.0",
                            "endpoints" => [
                                "/api/logs" => [
                                    "name" => "Logs",
                                    "description" => "An endpoint for querying the Plugin's log files."
                                ]
                            ]
                        ],
                        200,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }
            );

        })->add(
            function (Request $request, Response $response, $next)
            use ($app)
            {
                if(Plugin::mode() === Plugin::MODE_DEVELOPMENT)
                {
                    $path = $request->getUri()
                        ->getPath();
                    $query = $request->getUri()
                        ->getQuery();

                    $message = $path.($query !== "" ? "?$query" : "");
                    Log::debug($message, Log::REST);
                }

                return $next($request, $response);
            }
        );

    }

}
