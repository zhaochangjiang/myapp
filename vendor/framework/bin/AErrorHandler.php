<?php

namespace framework\bin;

use framework\bin\ARequest;
use framework\bin\AResponse;
use framework\App;
use framework\bin\ADBException;

/**
 * 错误异常处理
 *
 * @author heypigg
 * #
 */
class AErrorHandler
{

    private $request;
    private $response;

    public function __construct()
    {
        $this->request = new ARequest();
        $this->response = new AResponse();
    }

    /*
     * 处理异常
     * @param Exception $exception 捕获异常
     */

    public function handleException(Exception $exception)
    {
        $trace = array_slice($exception->getTrace(), 0, 5);
        //$trace = $this->getExactTrace($exception);
        $fileName = $exception->getFile();
        $errorLine = $exception->getLine();

        foreach ((array)$trace as $i => $t) {
            if (!isset($t['file']))
                $trace[$i]['file'] = 'unknown';

            if (!isset($t['line']))
                $trace[$i]['line'] = 0;

            if (!isset($t['function']))
                $trace[$i]['function'] = 'unknown';

            unset($trace[$i]['object']);
        }

        $data = array(
            'code' => ($exception instanceof HttpException) ? $exception->getStatusCode() : 500,
            'type' => get_class($exception),
            'errorCode' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $fileName,
            'line' => $errorLine,
            'trace' => $exception->getTraceAsString(),
            'traces' => $trace,
        );

        if (!headers_sent())
            $this->response
                ->status($data['code'])
                ->sendHeaders();

        if (!IS_DEBUG) {
            if ($exception instanceof ADBException) {
                $data['message'] = '';
            }
            $this->render('error', $data);
        } else {
            if ($this->isAjaxRequest())
                $this->displayException($exception);
            else
                $this->render('exception', $data);
        }
    }

    /**
     * 捕获php错误到ErrorException
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @return bool
     */
    public function handleError($level, $message, $file = null, $line = null)
    {
        if ($level & $this->isLevelFatal($level)) {
            $exception = new \ErrorException($message, $level, 0, $file, $line);
            if ($this->canThrowExceptions) {
                $trace = debug_backtrace();
                if (count($trace) > 3)
                    $trace = array_slice($trace, 5);
                $traceString = '';
                foreach ($trace as $i => $t) {
                    if (!isset($t['file']))
                        $trace[$i]['file'] = 'unknown';

                    if (!isset($t['line']))
                        $trace[$i]['line'] = 0;

                    if (!isset($t['function']))
                        $trace[$i]['function'] = 'unknown';

                    $traceString .= "#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
                    if (isset($t['object']) && is_object($t['object']))
                        $traceString .= get_class($t['object']) . '->';
                    $traceString .= "{$trace[$i]['function']}()\n";

                    unset($trace[$i]['object']);

                    switch ($level) {
                        case E_WARNING:
                            $type = 'PHP warning';
                            break;
                        case E_NOTICE:
                            $type = 'PHP notice';
                            break;
                        case E_USER_ERROR:
                            $type = 'User error';
                            break;
                        case E_USER_WARNING:
                            $type = 'User warning';
                            break;
                        case E_USER_NOTICE:
                            $type = 'User notice';
                            break;
                        case E_RECOVERABLE_ERROR:
                            $type = 'Recoverable error';
                            break;
                        default:
                            $type = 'PHP error';
                    }

                    $data = array(
                        'code' => 500,
                        'type' => $type,
                        'message' => $message,
                        'file' => $file,
                        'line' => $line,
                        'trace' => $traceString,
                        'traces' => $trace,
                        'time' => time()
                    );

                    if (!headers_sent())
                        $this->response
                            ->status($data['code'])
                            ->sendHeaders();

                    if ($this->isAjaxRequest())
                        $this->displayError($level, $message, $file, $line);
                    elseif (IS_DEBUG)
                        $this->render('exception', $data);
                    else
                        $this->render('error', $data);
                }
            } else {
                $this->handleException($exception);
            }
        }
    }

    /**
     * Special case to deal with Fatal errors and the like.
     */
    public function handleShutdown()
    {
        // If we reached this step, we are in shutdown handler.
        // An exception thrown in a shutdown handler will not be propagated
        // to the exception handler. Pass that information along.
        $this->canThrowExceptions = false;

        $error = error_get_last();

        if ($error && $this->isLevelFatal($error['type'])) {

            $this->handleError(
                $error['type'], $error['message'], $error['file'], $error['line']
            );
        }
    }

    /**
     * 显示捕获的php错误 PHP error.
     * @param integer $code error code
     * @param string $message error message
     * @param string $file error file
     * @param string $line error line
     */
    public function displayError($code, $message, $file, $line)
    {
        if (IS_DEBUG) {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message ($file:$line)</p>\n";
            echo '<pre>';

            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                echo "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    echo get_class($t['object']) . '->';
                echo "{$t['function']}()\n";
            }

            echo '</pre>';
        } else {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message</p>\n";
        }
    }

    /**
     * 显示捕获到的PHP异常.
     * @param Exception $exception the uncaught exception
     */
    public function displayException($exception)
    {
        if (IS_DEBUG) {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        } else {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . '</p>';
        }
    }

    protected function getExactTrace($exception)
    {
        $traces = $exception->getTrace();

        foreach ($traces as $trace) {
            // property access exception
            if (isset($trace['function']) && ($trace['function'] === '__get' || $trace['function'] === '__set'))
                return $trace;
        }
        return null;
    }

    protected function render($view, $data)
    {

        // additional information to be passed to view
        $data['version'] = $this->getVersionInfo();
        $data['time'] = time();
        if ($view == 'error') {
            $view = 'error' . $data['code'];
            $data['version'] = '';
        }
        if (file_exists(App::getPathOfAlias('application.template.error') . D_S . $view . '.php')) {
            App::base()->controller->loadViewCell('error/' . $view);
        } else {
            $path = App::getSystemViewPath() . D_S . $view . '.php';
            $data['admin'] = '';
            include_once($path);
        }
        exit();
    }

    protected function getVersionInfo()
    {
        if (IS_DEBUG) {
            $version = '';
            if (isset($_SERVER['SERVER_SOFTWARE']))
                $version = $_SERVER['SERVER_SOFTWARE'] . ' ' . $version;
        } else
            $version = '';
        return $version;
    }

    /**
     * Returns a value indicating whether the call stack is from application code.
     * @param array $trace the trace data
     * @return boolean whether the call stack is from application code.
     */
    protected function isCoreCode($trace)
    {
        if (isset($trace['file'])) {
            $systemPath = realpath(dirname(__FILE__) . '/..');
            return $trace['file'] === 'unknown' || strpos(realpath($trace['file']), $systemPath . DIRECTORY_SEPARATOR) === 0;
        }
        return false;
    }

    /**
     * Converts arguments array to its string representation
     *
     * @param array $args arguments array to be converted
     * @return string string representation of the arguments array
     */
    protected function argumentsToString($args)
    {
        $count = 0;

        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5)
                    unset($args[$key]);
                else
                    $args[$key] = '...';
                continue;
            }

            if (is_object($value))
                $args[$key] = get_class($value);
            elseif (is_bool($value))
                $args[$key] = $value ? 'true' : 'false';
            elseif (is_string($value)) {
                if (strlen($value) > 64)
                    $args[$key] = '"' . substr($value, 0, 64) . '..."';
                else
                    $args[$key] = '"' . $value . '"';
            } elseif (is_array($value))
                $args[$key] = 'array(' . $this->argumentsToString($value) . ')';
            elseif ($value === null)
                $args[$key] = 'null';
            elseif (is_resource($value))
                $args[$key] = 'resource';

            if (is_string($key)) {
                $args[$key] = '"' . $key . '" => ' . $args[$key];
            } elseif ($isAssoc) {
                $args[$key] = $key . ' => ' . $args[$key];
            }
        }
        $out = implode(", ", $args);

        return $out;
    }

    /**
     * @var integer maximum number of source code lines to be displayed. Defaults to 25.
     */
    public $maxSourceLines = 25;

    /**
     * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
     * @since 1.1.6
     */
    public $maxTraceSourceLines = 10;

    /**
     * Renders the source code around the error line.
     * @param string $file source file path
     * @param integer $errorLine the error line number
     * @param integer $maxLines maximum number of lines to display
     * @return string the rendering result
     */
    protected function renderSourceCode($file, $errorLine, $maxLines)
    {
        $errorLine--; // adjust line number to 0-based from 1-based
        if ($errorLine < 0 || ($lines = @file($file)) === false || ($lineCount = count($lines)) <= $errorLine)
            return '';

        $halfLines = (int)($maxLines / 2);
        $beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
        $endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;
        $lineNumberWidth = strlen($endLine + 1);

        $output = '';
        for ($i = $beginLine; $i <= $endLine; ++$i) {
            $isErrorLine = $i === $errorLine;
            $code = sprintf("<span class=\"ln" . ($isErrorLine ? ' error-ln' : '') . "\">%0{$lineNumberWidth}d</span> %s", $i + 1, str_replace("\t", '    ', $lines[$i]));
            if (!$isErrorLine)
                $output .= $code;
            else
                $output .= '<span class="error">' . $code . '</span>';
        }
        return '<div class="code"><pre>' . $output . '</pre></div>';
    }

    /**
     * whether the current request is an AJAX (XMLHttpRequest) request.
     * @return boolean whether the current request is an AJAX request.
     */
    protected function isAjaxRequest()
    {
        return $this->request->ajax;
    }

    private $canThrowExceptions = true;

    private static function isLevelFatal($level)
    {
        return in_array(
            $level, array(
                E_ERROR,
                E_PARSE,
                E_WARNING,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING
            )
        );
    }

}
  