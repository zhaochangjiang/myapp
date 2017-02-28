<?php
namespace framework\lib;

/**
 *
 * @author zhaocj
 *         XML文件解析
 */
class XMLParser
{

    private $filename;

    private $xml;

    private $data;

    private function __construct ( )
    {
    
    }

    public static function parseXml ( $xml_file )
    {
        if ( !file_exists ( $xml_file ) )
        {
            die ( 'Cannot find XML data file: '.$xml_file );
        }
        $o = new XMLParser ( );
        $data= $o -> parse ( $xml_file );
        return $data;
    }

    private function parse ( $xml_file )
    {
        $this->filename = $xml_file;
        $this->xml = xml_parser_create ( );
        xml_set_object ( $this->xml , $this );
        xml_set_element_handler ( $this->xml , 'startHandler' , 'endHandler' );
        xml_set_character_data_handler ( $this->xml , 'dataHandler' );
        if ( !( $fp = fopen ( $this->filename , 'r' ) ) )
        {
            die ( 'Cannot open XML data file: '.$this->filename );
            return false;
        }
        
        $bytes_to_parse = 1024;
        
        while ( $data = fread ( $fp , $bytes_to_parse ) )
        {
            $parse = xml_parse ( $this->xml , $data , feof ( $fp ) );
            
            if ( !$parse )
            {
                die ( sprintf ( "XML error: %s at line %d" , xml_error_string ( xml_get_error_code ( $this->xml ) ) , xml_get_current_line_number ( $this->xml ) ) );
                xml_parser_free ( $this->xml );
            }
        }
        return $this->data;
    }

    private function startHandler ( $parser , $name , $attributes )
    {
        // $data [ 'name' ] = strtolower($name);
        if ( $attributes )
        {
            $data [ 'attributes' ] = $attributes;
        }
        $this->data [ ] = $data;
    }

    private function dataHandler ( $parser , $data )
    {
        if ( $data = trim ( $data ) )
        {
            $index = count ( $this->data )-1;
            // begin multi-line bug fix (use the .= operator)
            $this->data [ $index ] [ 'content' ] .= $data;
            // end multi-line bug fix
        }
    }

    private function endHandler ( $parser , $name )
    {
        if ( count ( $this->data )>1 )
        {
            $data = array_pop ( $this->data );
            $index = count ( $this->data )-1;
            // xmp($data);
            // 处理值为空的情况
            if ( empty ( $data )||isset ( $data [ 'content' ] ) )
            {
                // empty($data [ 'content' ])?$data [ 'content' ]='':'';
                $this->data [ $index ] [ strtolower ( $name ) ] = $data [ 'content' ];
            }
            else
            {
                $this->data [ $index ] [ strtolower ( $name ) ] [ ] = $data;
            }
        }
    }
}