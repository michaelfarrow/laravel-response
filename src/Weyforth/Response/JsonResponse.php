<?php
/**
 * Json Response.
 *
 * Json Response object that mimics the structure of Laravel's 
 * RedirectResponse object. Common methods include 'withInput', 
 * 'withErrors' and 'with'. Useful for providing ajax support
 * with fallback to normal request & redirect flow.
 *
 * @author    Mike Farrow <contact@mikefarrow.co.uk>
 * @license   Proprietary/Closed Source
 * @copyright Mike Farrow
 */

namespace Weyforth\Response;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{

    /**
     * Whether the response is a success or failure.
     *
     * @var boolean
     */
    protected $success = true;

    /**
     * Message to include with the response.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Whether input should be send with the response.
     *
     * @var boolean
     */
    protected $input = false;

    /**
     * Whether errors should be send with the response.
     *
     * @var boolean
     */
    protected $withErrors = false;

    /**
     * The errors to include with the response.
     *
     * @var array|Illuminate\Support\MessageBag
     */
    protected $errors = array();

    /**
     * The custom data to include with the response.
     *
     * @var array
     */
    protected $jsonData = array();


    /**
     * Constructor.
     *
     * @param boolean $success Whether the success is a success response or a fail response.
     * @param string  $message Message string to send with the response.
     *
     * @return void
     */
    public function __construct($success, $message = '')
    {
        parent::__construct();
        $this->success = $success;
        $this->message = $message;
    }


    /**
     * Shortcut function to create a success response.
     *
     * @param string $message Message string to send with the response.
     *
     * @return Weyforth\Response\JsonResponse
     */
    public static function success($message)
    {
        return new static(true, $message);
    }


    /**
     * Shortcut function to create a fail response.
     *
     * @param string $message Message string to send with the response.
     *
     * @return Weyforth\Response\JsonResponse
     */
    public static function fail($message)
    {
        return new static(false, $message);
    }


    /**
     * Add the input variables to the response.
     *
     * @return Weyforth\Response\JsonResponse
     */
    public function withInput()
    {
        $this->input = true;

        return $this;
    }


    /**
     * Add the supplied errors to the response.
     *
     * @param array|Illuminate\Support\MessageBag $errors Errors to add to the response.
     *
     * @return Weyforth\Response\JsonResponse
     */
    public function withErrors($errors)
    {
        $this->withErrors = true;
        $this->errors     = $errors;

        return $this;
    }


    /**
     * Add the supplied data to the response.
     *
     * @param string|array $key   Key or array of key-value pairs.
     * @param string|array $value Value if key is specified.
     *
     * @return Weyforth\Response\JsonResponse
     */
    public function with($key, $value = null)
    {
        $args  = func_get_args();
        $count = count($args);

        switch ($count) {
            case 1:
                if (is_array($args[0])) {
                    $this->jsonData = array_merge($this->jsonData, $args[0]);
                }
                break;

            case 2:
                if (is_string($args[0])) {
                    $this->jsonData[$args[0]] = $args[1];
                }
                break;
        }

        return $this;
    }


    /**
     * Compiles the data to include in the response.
     *
     * @return void
     */
    protected function compileJsonContent()
    {
        $response = array(
            'success' => $this->success
        );

        if ($this->withErrors) {
            $response['errors'] = array();
            $response['error']  = '';

            if (count($this->errors) > 0) {
                if ($this->errors instanceof \Illuminate\Support\MessageBag) {
                    $response['errors'] = $this->errors->all();
                    $response['error']  = $this->errors->first();
                } else {
                    $response['errors'] = $this->errors;
                    $response['error']  = $this->errors[0];
                }
            }
        }

        if ($this->input) {
            $response['input'] = Input::all();
        }

        $response['message'] = $this->message;

        $response = array_merge($response, $this->jsonData);

        $this->setData($response);
    }


    /**
     * Compiles the data and sends the content.
     *
     * @return void
     */
    public function sendContent()
    {
        $this->compileJsonContent();

        parent::sendContent();
    }


    /**
     * Compiles the data and gets the content.
     *
     * @return string
     */
    public function getContent()
    {
        $this->compileJsonContent();

        return parent::getContent();
    }


}
