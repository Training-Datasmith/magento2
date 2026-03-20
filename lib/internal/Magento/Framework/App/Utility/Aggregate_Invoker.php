<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Utility;

/**
 * Runs given callback across given array of data and collects all PhpUnit assertion results.
 * Should be used in case data provider is huge to minimize overhead.
 */
class Aggregate_Invoker
{
    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $_test_case;
    /**
     * There is no PHPUnit internal API to determine whether --verbose or --debug options are passed.
     * When verbose is true, data sets are gathered for any result, includind incomplete and skipped test.
     * Only data sets for failed assertions are gathered otherwise.
     *
     * @var array
     */
    protected $_options = ['verbose' => false];
    /**
     * @param \PHPUnit\Framework\TestCase $testCase
     * @param array $options
     */
    public function __construct($test_case, array $options = [])
    {
        $this->_test_case = $test_case;
        $this->_options = $options + $this->_options;
    }
    /**
     * Collect all failed assertions and fail test in case such list is not empty.
     *
     * Incomplete and skipped test results are aggregated as well.
     *
     * @param callable $callback
     * @param array[] $dataSource
     * @return void
     */
    public function __invoke(callable $callback, array $data_source)
    {
        $results = [\Php_Unit\Framework\Incomplete_Test_Error::class => [], \Php_Unit\Framework\Skipped_With_Message_Exception::class => [], \Php_Unit\Framework\Assertion_Failed_Error::class => []];
        $passed = 0;
        foreach ($data_source as $data_set_name => $data_set) {
            try {
                call_user_func_array($callback, $data_set);
                $passed++;
            } catch (\Php_Unit\Framework\Incomplete_Test_Error $exception) {
                $results[get_class($exception)][] = $this->prepare_message($exception, $data_set_name, $data_set);
            } catch (\Php_Unit\Framework\Skipped_With_Message_Exception $exception) {
                $results[get_class($exception)][] = $this->prepare_message($exception, $data_set_name, $data_set);
            } catch (\Php_Unit\Framework\Assertion_Failed_Error $exception) {
                $results[\Php_Unit\Framework\Assertion_Failed_Error::class][] = $this->prepare_message($exception, $data_set_name, $data_set);
            }
        }
        $this->process_results($results, $passed);
    }
    /**
     * Prepare Message
     *
     * @param \Exception $exception
     * @param string $dataSetName
     * @param mixed $dataSet
     * @return string
     */
    protected function prepare_message(\Exception $exception, $data_set_name, $data_set)
    {
        if (!is_string($data_set_name)) {
            $data_set_name = var_export($data_set, true);
        }
        if ($exception instanceof \Php_Unit\Framework\Assertion_Failed_Error && !$exception instanceof \Php_Unit\Framework\Incomplete_Test_Error && !$exception instanceof \Php_Unit\Framework\Skipped_With_Message_Exception || $this->_options['verbose']) {
            $data_set_name = 'Data set: ' . $data_set_name . PHP_EOL;
        } else {
            $data_set_name = '';
        }
        return $data_set_name . $exception->get_message() . PHP_EOL . $exception->get_trace_as_string();
    }
    /**
     * Analyze results of aggregated tests execution and complete test case appropriately
     *
     * @param array $results
     * @param int $passed
     * @return void
     */
    protected function process_results(array $results, $passed)
    {
        $total_counts_message = sprintf('Passed: %d, Failed: %d, Incomplete: %d, Skipped: %d.', $passed, count($results[\Php_Unit\Framework\Assertion_Failed_Error::class]), count($results[\Php_Unit\Framework\Incomplete_Test_Error::class]), count($results[\Php_Unit\Framework\Skipped_With_Message_Exception::class]));
        if ($results[\Php_Unit\Framework\Assertion_Failed_Error::class]) {
            $this->_test_case->fail($total_counts_message . PHP_EOL . implode(PHP_EOL, $results[\Php_Unit\Framework\Assertion_Failed_Error::class]));
        }
        if (!$results[\Php_Unit\Framework\Incomplete_Test_Error::class] && !$results[\Php_Unit\Framework\Skipped_With_Message_Exception::class]) {
            return;
        }
        $message = $total_counts_message . PHP_EOL . implode(PHP_EOL, $results[\Php_Unit\Framework\Incomplete_Test_Error::class]) . PHP_EOL . implode(PHP_EOL, $results[\Php_Unit\Framework\Skipped_With_Message_Exception::class]);
        if ($results[\Php_Unit\Framework\Incomplete_Test_Error::class]) {
            $this->_test_case->mark_test_skipped($message);
        } elseif ($results[\Php_Unit\Framework\Skipped_With_Message_Exception::class]) {
            $this->_test_case->mark_test_skipped($message);
        }
    }
}