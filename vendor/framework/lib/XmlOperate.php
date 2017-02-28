<?php
namespace framework\lib;
class XmlOperate
{

    var $itemKey = array();

    /**
     * @param  $param - 
     * @example = array (
      array (
      'title' 			=> 'title1',
      'content' 		=> 'content1',
      'pubdate' 	=> '2009-10-11'
      ),
      array (
      'title' 			=> 'title1',
      'content' 		=> array (
      'val' 		=> 'content1',
      'size' 	=> '100',
      'title' 	=> '您好'
      ),
      'pubdate' 	=> '2009-10-11'
      ))
     * @param string $targetDirectoryFile - String 文件的名称
     * @throws Exception
     */
    public static function createXml($param, $targetDirectoryFile = '')
    {
        $xmlOperate = new XmlOperate();
        if (empty($param))
            return;
        $stuff      = '  ';
        $xml        = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xml .= "<article>\n";
        foreach ($param as $data)
        {
            if (empty($xmlOperate->itemKey))
            {
                $xmlOperate->itemKey = array_keys($data);
                if (empty($xmlOperate->itemKey))
                    throw new Exception('您输入的数据有异常！');
            }
            $xml .= $xmlOperate->create_item($data,
                                             $xmlOperate->itemKey,
                                             $stuff);
        }
        $xml .= "</article>\n";
        if (!empty($targetDirectoryFile))
        {
            $xmlFileName = "{$targetDirectoryFile}.xml";
            file_put_contents($xmlFileName,
                              $xml);
            return $xmlFileName;
        } else
        {
            echo $xml;
        }
    }

    /**
     * 解析XML文件获得想要的数据
     * @param  $sourceDirectoryFile
     */
    public static function analyzeXml($sourceDirectoryFile, $node = 'article')
    {
        if (!file_exists($sourceDirectoryFile))
            die("The is not the analyzeXml file:{$sourceDirectoryFile}!");
        $current  = array();
        $forecast = array();
        $reader   = new XMLReader();
        $reader->open($sourceDirectoryFile,
                      'utf-8');
        while ($reader->read())
        {
            //get current data
            if ($reader->name == $node && $reader->nodeType == XMLReader::ELEMENT)
            {
                while ($reader->read() && $reader->name != $node)
                {
                    $name           = $reader->name;
                    $value          = $reader->getAttribute('data');
                    $current[$name] = $value;
                }
            }

            //get forecast data
            if ($reader->name == "forecast_conditions" && $reader->nodeType == XMLReader::ELEMENT)
            {
                $sub_forecast = array();
                while ($reader->read() && $reader->name != "forecast_conditions")
                {
                    $name                = $reader->name;
                    $value               = $reader->getAttribute('data');
                    $sub_forecast[$name] = $value;
                }
                $forecast[] = $sub_forecast;
            }
        }
        $reader->close();
        print_r($current);
    }

    // 创建XML单项
    private function create_item($data, $itemKey, $stuff, $node = 'item')
    {
        $item     = "{$stuff}<{$node}>\n";
        $stuffStr = "{$stuff}  ";
        foreach ($itemKey as $k => $v)
        {
            if (!isset($data [$v]))
                continue;
            if (is_array($data [$v]))
            {
                $tempValue = $extend    = '';
                foreach ($data [$v] as $key => $value)
                {
                    if ('val' === $key)
                    {
                        $tempValue = $value;
                        continue;
                    } else
                        $extend .= " {$key}=\"{$value}\"";
                }
                $item .= "{$stuffStr}<{$v}{$extend}>{$tempValue}</{$v}>\n";
            } else
                $item .= "{$stuffStr}<{$v}>{$data[$v]}</{$v}>\n";
        }
        $item .= "{$stuff}</{$node}>\n";
        return $item;
    }

}

$xmlOperate = new XmlOperate ();
$data_array = array(
    array(
        'title'   => 'title1',
        'content' => 'content1',
        'pubdate' => '2009-10-11'
    ),
    array(
        'title'   => 'title1',
        'content' => array(
            'val'   => 'content1',
            'size'  => '100',
            'title' => '您好'
        ),
        'pubdate' => '2009-10-11'
    ),
    array(
        'title'   => 'title2',
        'content' => 'content2',
        'pubdate' => '2009-11-11'
    )
);
//XmlOperate::createXml ( $data_array,'./test' );
XmlOperate::analyzeXml('./test.xml');
?>