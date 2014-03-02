<?php

namespace ModernMedia\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * Class HtmlFormatter
 * @package ModernMedia\Monolog\Formatter
 */
class HtmlFormatter implements FormatterInterface {
    protected $styles = array(
        'table' => array(
            'max-width: 100%;',
            'background-color: transparent;',
            'border-collapse: collapse;',
            'border-spacing: 0;',
        ),
        'td' => array(
            'padding: 4px 5px;',
            'line-height: 20px;',
            'text-align: left;',
            'vertical-align: top;',
            'border-top: 1px solid #dddddd;'
        ),
        'pre,code' => array(
            'padding: 0 3px 2px;',
            'font-size: 12px;',
            'color: #333333;',
            'border-radius: 3px;'
        ),
        'code' => array(
            'padding: 2px 4px;',
            'color: #d14;',
            'white-space: nowrap;',
            'background-color: #f7f7f9;',
            'border: 1px solid #e1e1e8;'
        ),
        'pre' => array(
            'display: block;',
            'padding: 9.5px;',
            'margin: 0 0 10px;',
            'font-size: 13px;',
            'line-height: 20px;',
            'word-break: break-all;',
            'word-wrap: break-word;',
            'white-space: pre;',
            'white-space: pre-wrap;',
            'background-color: #f5f5f5;',
            'border: 1px solid #ccc;',
            'border: 1px solid rgba(0, 0, 0, 0.15);',
            'border-radius: 4px;'
        ),
        'label,badge' => array(
            'display: inline-block;',
            'padding: 2px 4px;',
            'font-weight: bold;',
            'color: #ffffff;',
            'text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);',
            'white-space: nowrap;',
            'vertical-align: baseline;',
            'background-color: #999999;'
        ),
        'label' => array(
            'border-radius: 3px;'
        ),
        'badge' => array(
            'padding-right: 9px;',
            'padding-left: 9px;',
            'border-radius: 9px;'
        )
    ];

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records) {
        $output = array();

        foreach($records as $record) {
            $output[] = $this->format($record);
        }

        return implode('<hr/>', $output);
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record) {
        ob_start();
        var_dump($record);
        $varDump = ob_get_clean();

        // Make sure var_dump is not overridden by Xdebug before tweaking its output.
        // Note that all truthy INI values ("On", "true", 1) are returned as "1" by ini_get().
        $hasXdebugVarDump = extension_loaded('xdebug') && ini_get('xdebug.overload_var_dump') === '1';
        if(!$hasXdebugVarDump) {
            $varDump = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $varDump);
            $varDump = '<pre style="' . $this->getStyle('pre') . '">' . htmlentities($varDump) . '</pre>';
        } else {
            $varDump = str_replace("class='xdebug-var-dump'", 'style="' . $this->getStyle('pre') . '"', $varDump);
        }

        $output = [
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '<head>',
            '    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
            '    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>',
            '    <title>' . htmlentities($record['message']) . '</title>',
            '</head>',
            '<body>',
            '    <h1>',
            '        <span style="' . $this->getStyle('label') . ' background-color:' . $this->getAlertColor($record['level']) . '">',
            htmlentities($record['message']),
            '        </span>',
            '    </h1>',
            '    <pre>' . $varDump . '</pre>'
        ];

        if(isset($record['extra'])) {
            $output[] = '   <table style="' . $this->getStyle('table') . '">';
            foreach($record['extra'] as $key => $value) {
                $output[] = '       <tr>';
                $output[] = '           <td style="' . $this->getStyle('td') . '">' . htmlentities($key) . '</td>';
                $output[] = '           <td style="' . $this->getStyle('td') . '"><code style="' . $this->getStyle('code') . '">' . htmlentities($value) . '</code></td>';
                $output[] = '       </tr>';
            }
            $output[] = '   </table>';
        }
        $output[] = '</body>';
        $output[] = '</html>';
        return implode("\r\n", $output);
    }


    /**
     * Combines styles into a single-line string
     *
     * @param $type
     * @return string
     */
    protected function getStyle($type) {
        $outputStyles = array();
        foreach($this->styles as $el => $styles) {
            if(in_array($type, explode(',', $el))) {
                $outputStyles = array_merge($outputStyles, $styles);
            }
        }
        return implode(' ', $outputStyles);
    }

    /**
     * Assigns a color to each level of log records.
     *
     * @param integer $level
     * @return string
     */
    protected function getAlertColor($level) {
        switch(true) {
            case $level >= Logger::ERROR:
                return '#b94a48';
            case $level >= Logger::WARNING:
                return '#f89406';
            case $level >= Logger::INFO:
                return '#3a87ad';
            default:
                return '#999999';
        }
    }

}
