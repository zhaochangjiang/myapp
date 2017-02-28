<?php
namespace framework\lib;
/**
* 基本图片处理，用于完成图片缩入，水印添加
* 当水印图超过目标图片尺寸时，水印图能自动适应目标图片而缩小
* 水印图可以设置跟背景的合并度
* @author zhaocj
* @version 1.0
* @date 2012-02-14
* @example  使用方法:
自动裁切:
程序会按照图片的尺寸从中部裁切最大的正方形，并按目标尺寸进行缩略
$t->setSrcImg("img/test.jpg");
$t->setCutType(1);//这一句就OK了
$t->setDstImg("tmp/new_test.jpg");
$t->createImg(60,60);
手工裁切:
程序会按照指定的位置从源图上取图
$t->setSrcImg("img/test.jpg");
$t->setCutType(2);//指明为手工裁切
$t->setSrcCutPosition(100, 100);// 源图起点坐标
$t->setRectangleCut(300, 200);// 裁切尺寸
$t->setDstImg("tmp/new_test.jpg");
$t->createImg(300,200); 
*/
class ImageDeal{   
    var $dst_img;						// 目标文件
    var $h_src; 							// 图片资源句柄
    var $h_dst;							// 新图句柄
    var $h_mask;						// 水印句柄
    var $img_display_quality = 99;		// 图片显示质量,默认为75
    var $img_scale 			 = 0;		// 图片缩放比例
    var $src_w = 0;						// 原图宽度
    var $src_h = 0;						// 原图高度
    var $src_info = null;				// 原图的 属性
    var $dst_w = 0;						// 新图总宽度
    var $dst_h = 0;						// 新图总高度
    var $fill_w;						// 填充图形宽
    var $fill_h;						// 填充图形高
    var $copy_w;						// 拷贝图形宽
    var $copy_h;						// 拷贝图形高
    var $src_x = 0;						// 原图绘制起始横坐标
    var $src_y = 0;						// 原图绘制起始纵坐标
    var $start_x;						// 新图绘制起始横坐标
    var $start_y;						// 新图绘制起始纵坐标
    var $mask_word;						// 水印文字
    var $mask_img;						// 水印图片
    var $mask_pos_x 	= 0;			// 水印横坐标
    var $mask_pos_y 	= 0;			// 水印纵坐标
    var $mask_offset_x 	= 5;			// 水印横向偏移
    var $mask_offset_y 	= 5;			// 水印纵向偏移
    var $font_w;						// 水印字体宽
    var $font_h;						// 水印字体高
    var $mask_w;						// 水印宽
    var $mask_h;						// 水印高
    var $mask_font_color = "#ffffff";// 水印文字颜色
    var $mask_font 		= 2;		// 水印字体
    var $font_size;						// 尺寸
    var $mask_position 	= 0;		// 水印位置
    var $mask_img_pct 	= 50;		// 图片合并程度,值越大，合并程序越低
    var $mask_txt_pct 	= 50;		// 文字合并程度,值越小，合并程序越低
    var $img_border_size = 0;		// 图片边框尺寸
    var $img_border_color;				// 图片边框颜色
    var $_flip_x 		= 0;		// 水平翻转次数
    var $_flip_y 		= 0;		// 垂直翻转次数
    var $cut_type 		= 0;			// 剪切类型
    var $img_type;						// 文件类型
    var $fontstyle;						// 字体样式
    var $all_type = array( // 文件类型定义,并指出了输出图片的函数
					        "jpg"  => array("output" => "imagejpeg"),
					        "gif"  => array("output" => "imagegif"),
					        "png"  => array("output" => "imagepng"),
					        "wbmp" => array("output" => "image2wbmp"),
					        "jpeg" => array("output" => "imagejpeg")
					     );
    /**
     * 构造函数
     */
    function __construct(){
        $this->mask_font_color = "#ffffff";
        $this->font = 2;
        $this->font_size = 12;
    }
 
    /**
     * 取得图片的宽
     * @param  $src  - string 
     * @return number
     */
    function getImgWidth($src) {
        return imagesx($src);
    }

    /**
     * 取得图片的高
     * @param  $src  - string
     * @return number
     */
    function getImgHeight($src){
        return imagesy($src);
    }
 
    /**
     * @DESC 获得图片的 宽和高
     * @Date 2011-8-22
     * @param  $src  - string 
     * @return multitype:
     */
    function getImageInfo($src)
    {
    	return $this->src_info = @getimagesize($src);
    }
    /**
     * @DESC 	设置原始图片路径(背景图片)
     * @param   $src_img   - string图片生成路径
     * @param   $img_type  - String; 图片格式
     */
    function setSrcImg($src_img, $img_type=null){
        if(!file_exists($src_img)) {
            die("image类源图片:$src_img 不存在");
        }
        if(!empty($img_type)){
            $this->img_type = $img_type;
        }else{
            $this->img_type = $this->_getImgType($src_img);
        }
        $this->_checkValid($this->img_type);//检查文件类型合法性；
        $src = '';
        $handle = @fopen ($src_img, "r");
        if($handle){
          while (!feof ($handle)){
            $src .= fgets($handle, 4096);//未用过PHP5.0以下版本
          }
          fclose ($handle);
        }
        if(empty($src)){
            die("源图片$src为空");
        }
        $this->h_src = @ImageCreateFromString($src);
        $this->src_w = $this->getImgWidth($this->h_src);
        $this->src_h = $this->getImgHeight($this->h_src);
    }
    /**
     * 
     * 生成一张背景图
     * @param   $width   - int	背景图宽度
     * @param   $height  - int  背景图高度
     */
    function createpic($width,$height)
    {
    	 $this->h_src = @imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($this->h_src, 255, 255, 255);
        imagefill($this->h_src, 0, 0,$white);
        $this->src_w = $this->getImgWidth($this->h_src);
        $this->src_h = $this->getImgHeight($this->h_src);
        $this->img_type='png';
    }
    /**
     * @desc 设置图片生成路径
     * @param $dst_img  - string 图片生成路径
     */
    function setDstImg($dst_img){
        $arr  = explode('/',$dst_img);
        $last = array_pop($arr);
        $path = implode('/',$arr);
        $this->_mkdirs($path);
        $this->dst_img = $dst_img;
    }
    /**
     * 设置图片的显示质量
     *
     * @param  $n - string   质量
     */
    function setImgDisplayQuality($n){
        $this->img_display_quality = (int)$n;
    }
    /**
     * 设置文字水印
     *
     * @param $word  - string   水印文字
     * @param $font  - integer   水印字体
     * @param $fontstyle  -字体的样式数组array('iscut'=>true)
     * @param $color - string  水印字体颜色
     */
    function setMaskWord($word,$fontstyle=array())
    {
        $this->mask_word .= $word;
        $this->fontstyle = $fontstyle;
    }
 	
    /**
     * 设置字体颜色
     *
     * @param  $color  -string   字体颜色
     */
    function setMaskFontColor($color="#ffffff")
    {
        $this->mask_font_color = $color;
    }
    
    /**
     * 设置水印字体
     * @param $font - string|integer   字体
     */
    function setMaskFont($font=2){
        if(!is_numeric($font) && !file_exists($font)){
            die("$font字体文件不存在");
        }
        $this->font = $font;
    }
 
    /**
     * @desc 	设置文字字体大小,仅对truetype字体有效
     * @param  	$size - integer 
     */
    function setMaskFontSize($size = "12")
    {
        $this->font_size = $size;
    }
 
    /**
     * @desc    设置图片水印路径
     * @param   $img  - string  水印图片源
     */
    function setMaskImg( $img )
    {
        $this->mask_img = $img;
    }
 
    /**
     * 设置水印横向偏移
     * @param    integer     $x    横向偏移量
     */
    function setMaskOffsetX($x)
    {
        $this->mask_offset_x = intval($x);
    }
 
    /**
     * 设置水印纵向偏移
     * @param    integer     $y    纵向偏移量
     */
    function setMaskOffsetY($y) 
    {
        $this->mask_offset_y = intval($y);
    }
 
    /**
     * 指定水印位置
     * @param $position - integer   位置,1:左上,2:左下,3:右上,0/4:右下
     */
    function setMaskPosition( $position=0 )
    { 
        $this->mask_position = intval( $position );
    }
 
    /**
     * 设置图片合并程度
     * @param $n - integer    合并程度
     */
    function setMaskImgPct($n) {
        $this->mask_img_pct = (int)$n;
    }
 
    /**
     * 设置文字合并程度(透明度)
     * @param $n - integer    合并程度
     */
    function setMaskTxtPct($n){
        $this->mask_txt_pct = (int)$n;
    }
 
    /**
     * 设置缩略图边框
     *
     * @param    (类型)     (参数名)    (描述)
     */
    function setDstImgBorder($size=1, $color="#000000"){
        $this->img_border_size  = (int)$size;
        $this->img_border_color = $color;
    }
 
    /**
     * 水平翻转
     */
    function flipH(){
        $this->_flip_x++;
    }
 
    /**
     * 垂直翻转
     */
    function flipV(){
        $this->_flip_y++;
    }
 
    /**
     * @desc  设置剪切类型
     *
     * @param    (类型)     (参数名)    (描述)
     */
    function setCutType($type){
        $this->cut_type =intval($type);
     
    }
 
    /**
     * @desc 设置图片剪切
     * @param    integer     $width    矩形剪切
     */
    function setRectangleCut($width, $height){
        $this->fill_w = intval($width);
        $this->fill_h = intval($height);
    }
 
    /**
     * @desc  设置源图剪切起始坐标点
     * @param    (类型)     (参数名)    (描述)
     */
    function setSrcCutPosition($x, $y){
        $this->src_x  = intval($x);
        $this->src_y  = intval($y);
    }
 
    /**
     * @desc  创建图片,主函数
     * @param    integer    $a     当缺少第二个参数时，此参数将用作百分比，
     *                             否则作为宽度值
     * @param    integer    $b     图片缩放后的高度
     */
    function createImg($a=null, $b=null){
    	
        $num = func_num_args(); 
    	if($num==0||2 == $num)
        {
                	if($num==0)
                	{//如果一个参数都没有 则说明使用原始图片的长和宽
                		list($a, $b) = array($this->src_w,$this->src_h);
                		
                	}
                	$w = ( int ) $a;
                	
                    $h = ( int ) $b;
                 
                    if (0 == $w) {
                         die ( "目标宽度不能为0" );
                    }
                    if (0 == $h) {
                         die ( "目标高度不能为0" );
                    }
                    $this->_setNewImgSize ( $w, $h );
      	}elseif (1 == $num) {
                        $r = ( int ) $a;
                        if ($r < 1) {
                                die ( "图片缩放比例不得小于1" );
                        }
                        $this->img_scale = $r;
                        $this->_setNewImgSize ( $r );
                }else{
                	die('参数错误');
        }         
        if($this->_flip_x%2!=0){
            $this->_flipH($this->h_src);
        }
        if($this->_flip_y%2!=0){
            $this->_flipV($this->h_src);
        }
        $this->_createMask();
        $this->_output();
        // 释放
        if(imagedestroy($this->h_src) && imagedestroy($this->h_dst)){
            Return true;
        }else{
            Return false;
        }
    }
    
    /**
     * 生成水印,调用了生成水印文字和水印图片两个方法
     */
   private  function _createMask(){
        if($this->mask_word){
            // 获取字体信息
            $this->_setFontInfo();
           // if($this->_isFull()){
            //    die("水印文字过大");
           // }else{
                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);
                $white = ImageColorAllocate($this->h_dst,255,255,255);
                imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$white);// 填充背景色
                $this->_drawBorder();
                imagecopyresampled( $this->h_dst, $this->h_src,
                                    $this->start_x, $this->start_y,
                                    $this->src_x, $this->src_y,
                                    $this->fill_w, $this->fill_h,
                                    $this->copy_w, $this->copy_h);
                $this->_createMaskWord($this->h_dst);
          //  }
        }
        if($this->mask_img){
            $this->_loadMaskImg();//加载时，取得宽高
            if($this->_isFull()){
                // 将水印生成在原图上再拷
                $this->_createMaskImg($this->h_src);
                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);
                $white = ImageColorAllocate($this->h_dst,255,255,255);
                imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$white);// 填充背景色
                $this->_drawBorder();
                imagecopyresampled( $this->h_dst, $this->h_src,
                                    $this->start_x, $this->start_y,
                                    $this->src_x, $this->src_y,
                                    $this->fill_w, $this->start_y,
                                    $this->copy_w, $this->copy_h);
            }else {// 创建新图并拷贝
                $this->h_dst = imagecreatetruecolor($this->dst_w, $this->dst_h);
                $white = ImageColorAllocate($this->h_dst,255,255,255);
                imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$white);// 填充背景色
                $this->_drawBorder();
                imagecopyresampled( $this->h_dst, $this->h_src,
                                    $this->start_x, $this->start_y,
                                    $this->src_x, $this->src_y,
                                    $this->fill_w, $this->fill_h,
                                    $this->copy_w, $this->copy_h);
                $this->_createMaskImg($this->h_dst);
            }
        }
        if(empty($this->mask_word) && empty($this->mask_img)){
            $this->h_dst = @imagecreatetruecolor($this->dst_w, $this->dst_h)
                            or xmp("Cannot Initialize new GD image stream:{$this->dst_img}");
            $white = ImageColorAllocate($this->h_dst,255,255,255);
            imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$white);// 填充背景色
            $this->_drawBorder();
            imagecopyresampled( $this->h_dst, $this->h_src,
                        $this->start_x, $this->start_y,
                        $this->src_x, $this->src_y,
                        $this->fill_w, $this->fill_h,
                        $this->copy_w, $this->copy_h);
        }
    }
 
    /**
     * 画边框
     */
  private   function _drawBorder(){
        if(!empty($this->img_border_size)){
            $c = $this->_parseColor($this->img_border_color);
            $color = ImageColorAllocate($this->h_src,$c[0], $c[1], $c[2]);
            imagefilledrectangle($this->h_dst,0,0,$this->dst_w,$this->dst_h,$color);// 填充背景色
        }
    }
 
    /**
     * 生成水印文字
     */
   private  function _createMaskWord($src){
        $this->_countMaskPos();
        $this->_checkMaskValid();
        $c = $this->_parseColor($this->mask_font_color);
        $color = imagecolorallocatealpha($src, $c[0], $c[1], $c[2], $this->mask_txt_pct);
        if(is_numeric($this->font)){
            imagestring($src,
                        $this->font,
                        $this->mask_pos_x, $this->mask_pos_y,
                        $this->mask_word,
                        $color);
        }else{
        	if(!empty($this->fontstyle))
        	{
        		$this->addText($src);
        	}
        	else
        	{
        		$result = imagettftext($src,
                        $this->font_size, 0,
                        $this->mask_pos_x, $this->mask_pos_y+$this->font_size,
                        $color,
                        $this->font,
                        $this->mask_word);
        	}
        }
    }
    private function  addText($src)
    {
    		if(isset($this->fontstyle['iscut'])&&$this->fontstyle['iscut']===true)
    		{
    			$tmp = str_split($this->mask_word);
    			$result =array();
    			$temp = array();
    			$left = $top =0;
    			$c = $this->_parseColor($this->mask_font_color);
    			$len = count($tmp);
	        	for($i=0;$i<$len;$i++)
	        	{//$tmp[$i]==='-'?0:$range
	        		if(!empty($this->fontstyle['coloroffset']))
	        		{
		        		$randcolor= array(0,$this->fontstyle['coloroffset']);
		        		foreach ($c as $k=>$v)
		        		{ 
		        			$c[$k]=rand($v-$this->fontstyle['coloroffset']<0?0:$v-$this->fontstyle['coloroffset'], $v+$this->fontstyle['coloroffset']>255?255:$v+$this->fontstyle['coloroffset']);
		        		}
	        		}
	        		$range=($tmp[$i]==='-')?0:rand(-5, 5);
	        		if(empty($temp))
	        		{
       				 	$color = imagecolorallocatealpha($src, $c[0], $c[1], $c[2], $this->mask_txt_pct);
	        			$left =$this->mask_pos_x;
	        			$top  = $this->mask_pos_y+$this->font_size;
	        			$temp =imagettftext($src, $this->font_size, $range, $left, $top, $color, $this->font, $tmp[$i]);
	        		}
	        		else
	        		{
	        			$color = imagecolorallocatealpha($src, $c[0], $c[1], $c[2], $this->mask_txt_pct);
	        			$w = abs($temp[2]-$temp[0])>abs($temp[4]-$temp[6])?abs($temp[2]-$temp[0]):abs($temp[4]-$temp[6]);
	        			$h = abs($temp[1]-$temp[7])>abs($temp[3]-$temp[5])?abs($temp[1]-$temp[7]):abs($temp[3]-$temp[5]);
	        			$left=$left+$w;
	        			$temp   =imagettftext($src, $this->font_size, $range, $left, $top, $color, $this->font, $tmp[$i]);
	        		}
	        	}	
	        	
    		}
    		else
    		{
    			imagettftext($src,
                        $this->font_size, 0,
                        $this->mask_pos_x, $this->mask_pos_y+$this->font_size,
                        $color,
                        $this->font,
                        $this->mask_word);
    		}
    		
    }
    /**
     * 生成水印图
     */
   private  function _createMaskImg($src){
        $this->_countMaskPos();
        $this->_checkMaskValid();
        imagecopymerge($src,
                        $this->h_mask,
                        $this->mask_pos_x ,$this->mask_pos_y,
                        0, 0,
                        $this->mask_w, $this->mask_h,
                        $this->mask_img_pct);
 
        imagedestroy($this->h_mask);
    }
 
    /**
     * 加载水印图
     */
   private  function _loadMaskImg(){
        $mask_type = $this->_getImgType($this->mask_img);
        $this->_checkValid($mask_type);
        $handle = fopen ($this->mask_img, "r");
        $src='';
        while (!feof ($handle)){
            $src .= fgets($handle, 4096);
        }
        fclose ($handle);
        if(empty($this->mask_img)){
            die("{$this->mask_img}水印图片为空");
        }
        $this->h_mask = ImageCreateFromString($src);
        $this->mask_w = $this->getImgWidth($this->h_mask);
        $this->mask_h = $this->getImgHeight($this->h_mask);
    }
    
    /**
     * 图片输出
     */
  private   function _output(){
  		
        $func_name = $this->all_type[$this->img_type]['output'];
       
        if(function_exists($func_name)){
            // 判断浏览器,若是IE就不发送头
            if(isset($_SERVER['HTTP_USER_AGENT'])){
            	
                $ua = strtoupper($_SERVER['HTTP_USER_AGENT']);
                if(!preg_match('/^.*MSIE.*\)$/i',$ua)){
                    header("Content-type:{$this->img_type}");
                }
            }
            if($func_name==='imagepng')
            {
            	$func_name($this->h_dst, $this->dst_img);
            }else{
           	 	$func_name($this->h_dst, $this->dst_img, $this->img_display_quality);
            }
        }else{
            Return false;
        }
    }
    /**
     * 分析颜色
     * @param    string     $color    十六进制颜色
     */
   private  function _parseColor($color){
        $arr = array();
        for($ii=1; $ii<strlen ($color); $ii++){
            $arr[] = hexdec(substr($color,$ii,2));
            $ii++;
        }
        Return $arr;
    }
 
    /**
    * 计算出位置坐标
    */
   private  function _countMaskPos(){
        if($this->_isFull()){
            switch($this->mask_position){
                case 1:// 左上
                    $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                    $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                    break;
                case 2: // 左下
                    $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                    $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
                    break;
                case 3:   // 右上
                    $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                    $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                    break;
                 case 4:  // 右下
                    $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                    $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
                    break;  
                
                default:// 默认将水印放到右下,偏移指定像素
                    $this->mask_pos_x = $this->src_w - $this->mask_w - $this->mask_offset_x;
                    $this->mask_pos_y = $this->src_h - $this->mask_h - $this->mask_offset_y;
                    break;
            }
        }else{
            switch($this->mask_position){
            	case 0://左靠齐
            		$this->mask_pos_x = 0;
                    $this->mask_pos_y = ($this->src_h - $this->mask_h)/2+$this->img_border_size;
                    break;
                case 1:
                    // 左上
                    $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                    $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                    break;
                case 2:
                    // 左下
                    $this->mask_pos_x = $this->mask_offset_x + $this->img_border_size;
                    $this->mask_pos_y = $this->dst_h - $this->mask_h - $this->mask_offset_y - $this->img_border_size;
                    break;
 
                case 3:
                    // 右上
                    $this->mask_pos_x = $this->dst_w - $this->mask_w - $this->mask_offset_x - $this->img_border_size;
                    $this->mask_pos_y = $this->mask_offset_y + $this->img_border_size;
                    break;
 
                case 4:
                    // 右下
                    $this->mask_pos_x = $this->dst_w - $this->mask_w - $this->mask_offset_x - $this->img_border_size;
                    $this->mask_pos_y = $this->dst_h - $this->mask_h - $this->mask_offset_y - $this->img_border_size;
                    break;
 				 case 5 :// 中间
 				 	
                    $this->mask_pos_x = ($this->src_w - $this->mask_w)/2+$this->img_border_size;
                    $this->mask_pos_y = ($this->src_h - $this->mask_h)/2+$this->img_border_size;
                    break;  
                default:
                    // 默认将水印放到右下,偏移指定像素
                    $this->mask_pos_x = $this->dst_w - $this->mask_w - $this->mask_offset_x - $this->img_border_size;
                    $this->mask_pos_y = $this->dst_h - $this->mask_h - $this->mask_offset_y - $this->img_border_size;
                    break;
            }
        }
    }
 
    /**
    * 设置字体信息
    */
   private  function _setFontInfo(){
        if(is_numeric($this->font)){
            $this->font_w  = imagefontwidth($this->font);
            $this->font_h  = imagefontheight($this->font);
            $word_length   = strlen($this->mask_word);  // 计算水印字体所占宽高
            $this->mask_w  = $this->font_w*$word_length;
            $this->mask_h  = $this->font_h;
        }else{
            $arr = imagettfbbox ($this->font_size,0, $this->font,$this->mask_word);
            $this->mask_w  = abs($arr[0] - $arr[2]);
            $this->mask_h  = abs($arr[7] - $arr[1]);
        }
    }

 /**
   * 设置新图尺寸
   * @param    integer     $img_w   目标宽度
   * @param    integer     $img_h   目标高度
   */
  private   function _setNewImgSize($img_w, $img_h=null){
        $num = func_num_args();
        if(1 == $num){
            $this->img_scale = $img_w;// 宽度作为比例
            $this->fill_w = round($this->src_w * $this->img_scale/100) - $this->img_border_size*2;
            $this->fill_h = round($this->src_h * $this->img_scale/100) - $this->img_border_size*2;
            { // 源文件起始坐标
             $this->src_x  = 0;
             $this->src_y  = 0;
            }
            $this->copy_w = $this->src_w;
            $this->copy_h = $this->src_h;
            {// 目标尺寸
             $this->dst_w   = $this->fill_w + $this->img_border_size*2;
             $this->dst_h   = $this->fill_h + $this->img_border_size*2;
            }
        }elseif(2 == $num){
        
            $fill_w   = (int)$img_w - $this->img_border_size*2;
            $fill_h   = (int)$img_h - $this->img_border_size*2;
          
            if($fill_w < 0 || $fill_h < 0)
            {
                die("图片边框过大，已超过了图片的宽度");
            }
            $rate_w = $this->src_w/$fill_w;	//源图片 宽度与生成图片宽度的 比率
            $rate_h = $this->src_h/$fill_h;	//源图片 高度与生成图片高度的 比率
		
            switch($this->cut_type){
                case 0: 					// 如果原图大于缩略图，产生缩小，否则不缩小
                	if($rate_w < 1 && $rate_h < 1){
                        $this->fill_w = (int)$this->src_w;
                        $this->fill_h = (int)$this->src_h;
                    }else{
                        if($rate_w >= $rate_h){
                            $this->fill_w = (int)$fill_w;
                            $this->fill_h = round($this->src_h/$rate_w);
                        }else{
                            $this->fill_w = round($this->src_w/$rate_h);
                            $this->fill_h = (int)$fill_h;
                        }
                    }
                    $this->src_x   = $this -> src_y  =0;
                    $this->copy_w  = $this -> src_w;
                    $this->copy_h  = $this -> src_h;
                    $this->dst_w   = $this -> fill_w + $this -> img_border_size*2; // 目标尺寸
                    $this->dst_h   = $this -> fill_h + $this->img_border_size*2;
                    break;
                case 1: // 自动裁切  // 如果图片是先压缩，再在压缩的图片上裁剪相应的比例
                	$height = ceil($this->src_h*$img_w/$this->src_w);
                    if($rate_w >= 1 && $rate_h >=1){//压缩的图片尺寸小于生成的图片
                    	$this -> setSrcCutPosition(0, 0);
                        $this -> copy_w = $this -> src_w;
                        $this -> copy_h = $this -> src_h;
                        $this -> setRectangleCut($fill_w, $fill_h);
                    }else{
               
                        $this -> setSrcCutPosition(0, 0);           
                        //TODO 设置图片宽高让图片不失真。
                        if($height<$img_h){
                        	$img_h = $height;
                        }
                        $this -> setRectangleCut($img_w, $img_h);
                        $this -> copy_w = $this -> src_w;
                        $this -> copy_h = $this -> src_h;
                    }
                    $this->dst_w   = $this -> fill_w + $this -> img_border_size * 2;
                    $this->dst_h   = $this -> fill_h + $this -> img_border_size * 2;        
                    break;
                case 7: // 自动裁切 ，只裁切图片中间部分到指定宽度
                	$height = ceil($this->src_h*$img_w/$this->src_w);
                    if($rate_w >= 1 && $rate_h >=1){//压缩的图片尺寸小于生成的图片
                    	if($this->src_w > $this->src_h){
                            $src_x = round($this->src_w-$this->src_h)/2;
                            $this->setSrcCutPosition($src_x, 0);
                            $this->setRectangleCut($fill_w, $fill_h);
                            $this->copy_w = $this->src_h;
                            $this->copy_h = $this->src_h;
                        }elseif($this->src_w < $this -> src_h){
                            $src_y = round($this -> src_h-$this -> src_w)/2;
                            $this -> setSrcCutPosition(0, $src_y);
                            $this -> setRectangleCut($fill_w, $fill_h);
                            $this -> copy_w = $this -> src_w;
                            $this -> copy_h = $this -> src_w;
                        }else{
                            $this -> setSrcCutPosition(0, 0);
                            $this -> copy_w = $this -> src_w;
                            $this -> copy_h = $this -> src_w;
                            $this -> setRectangleCut($fill_w, $fill_h);
                        }
                    }else{
               
                        $this -> setSrcCutPosition(0, 0);           
                        //TODO 设置图片宽高让图片不失真。
                        if($height<$img_h){
                        	$img_h = $height;
                        }
                        $this -> setRectangleCut($img_w, $img_h);
                        $this -> copy_w = $this -> src_w;
                        $this -> copy_h = $this -> src_h;
                    }
                    $this->dst_w   = $this -> fill_w + $this -> img_border_size * 2;
                    $this->dst_h   = $this -> fill_h + $this -> img_border_size * 2;        
                    break;    
                    
                case 2:// 手工裁切  // 目标尺寸
           			
                    $this -> copy_w  = $this -> fill_w;
                    $this -> copy_h  = $this -> fill_h;
                    $this -> dst_w   = $this -> fill_w + $this -> img_border_size*2;
                    $this -> dst_h   = $this -> fill_h + $this -> img_border_size*2;                
                    break;
                default:
                    break;
            }
        }
        // 目标文件起始坐标
        $this->start_x = $this -> img_border_size;
        $this->start_y = $this -> img_border_size;
    }
 
   /**
    * 检查水印图是否大于生成后的图片宽高
    */
   private  function _isFull(){
   		
        return ($this->mask_w + $this->mask_offset_x > $this->fill_w|| $this->mask_h + $this -> mask_offset_y > $this->fill_h)?true:false;
    }
 
    /**
     * 检查水印图是否超过原图
     */
    private function _checkMaskValid(){
		if(!empty($this->mask_word))return;
        if($this->mask_w + $this -> mask_offset_x > $this -> src_w|| $this -> mask_h + $this -> mask_offset_y > $this->src_h){
            die("水印图片尺寸大于原图，请缩小水印图");
        }
    }
    /**
     * 取得图片类型
     *
     * @param    string     $file_path    文件路径
     */
    private function _getImgType($file_path){
        $type_list = array(
        				"1"=>"gif",
        				"2"=>"jpg",
        				"3"=>"png",
        				"4"=>"swf",
        				"5" =>"psd",
        				"6"=>"bmp",
        				"15"=>"wbmp"
        				);
        if(file_exists($file_path)){
            $img_info = @getimagesize ($file_path);//获得图片基础信息
            if(isset($type_list[$img_info[2]])){
             Return $type_list[$img_info[2]];
            }
        }else{
            die("{$file_path}文件不存在,不能取得文件类型!");
        }
    }
    
    /**
    * 检查图片类型是否合法,调用了array_key_exists函数，此函数要求
    * php版本大于4.1.0
    * @param    string     $img_type    文件类型
    */
   private function _checkValid($img_type){
       if(!array_key_exists($img_type, $this->all_type)){
            Return false;
       }
    }
    /**
     * 按指定路径生成目录
     * @param    string     $path    路径
     */
    private function _mkdirs($path){
        $adir = explode('/',$path);
        $dirlist = '';
        $rootdir = array_shift($adir);
        if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir)){
            @mkdir($rootdir);
        }
        if($adir){
	        foreach($adir as $key=>$val){
	            if($val!='.'&&$val!='..'){
	                $dirlist .= "/".$val;
	                $dirpath = $rootdir.$dirlist;
	                if(!file_exists($dirpath)){
	                    @mkdir($dirpath,0777);
	                }
	            }
	        }
   	 	}
    }
    
  /**
   * 垂直翻转
   * @param    string     $src    图片源
   */
   private function _flipV($src){
        $src_x = $this->getImgWidth($src);
        $src_y = $this->getImgHeight($src);
        $new_im = imagecreatetruecolor($src_x, $src_y);
        for ($y = 0; $y < $src_y; $y++){
            imagecopy($new_im, $src, 0, $src_y - $y - 1, 0, $y, $src_x, 1);
        }
        $this->h_src = $new_im;
    }
 
   /**
    * 水平翻转
    * @param    string     $src    图片源
    */
    private function _flipH($src){
        $src_x = $this->getImgWidth($src);
        $src_y = $this->getImgHeight($src);
        $new_im = imagecreatetruecolor($src_x, $src_y);
        for ($x = 0; $x < $src_x; $x++){
            imagecopy($new_im, $src, $src_x - $x - 1, 0, $x, 0, 1, $src_y);
        }
        $this->h_src = $new_im;
    }
    
    /**
     * @DESC 给出指定起始坐标 和图片宽高 裁剪裁剪图片
     * @Date 2012-02-14
     * @param Array $array example:$array= array(参数必须为此结构。
													'src' =>'flower2.jpg',
													'x1'  =>$_POST['x1'],
													'y1'  =>$_POST['y1'],
													'width'  =>$_POST['w'],
													'height'  =>$_POST['h'],
													'dst'		=> 目标生成路径 为空字符串则直接显示,不存在则默认原路径加上 _宽x高
												);
     */
    function cutImage($array){
    	$this->setCutType(2);										//0 按比例放大缩小图片 ，1自动剪切，2:手工剪切图片
		$this->setSrcCutPosition($array['x1'], $array['y1']);		//设置切割起始位置 
		$this->setRectangleCut($array['width'],$array['height']);	//设置切割图片 宽高 
		if(isset($array['dst']))
		{//生成的图片路径
			empty($array['dst'])?'':$this->setDstImg($array['dst']);
		}else
		{
			$filename = basename($array['src']);
    		$arr_temp = explode('.', $filename);
    		$file_type = array_pop($arr_temp);
    		$path = dirname($array['src']).'/'.implode('.', $arr_temp);
			if(preg_match('|(_[1-9][0-9]*x[1-9][0-9]*)$|', $path))
			{
				$dst_img = preg_replace('|(_[1-9][0-9]*x[1-9][0-9]*)$|', "_{$array['width']}x{$array['height']}", $path).".{$file_type}";
			}else
			{
				$dst_img =$path."_{$array['width']}x{$array['height']}.{$file_type}" ;
			}
			$this->setDstImg($dst_img);
		}
		$this->createImg($array['width'],$array['height']);			// 指定固定宽高
		return $dst_img;
    }
    
    /**
     * 将图片拉伸或者压缩成指定大小，保证不失真
     * @param  $source
     * @param  $val
     */
    function reallizebox($source,$val,$cutType=1)
    {  
    	$arr_dst_img =array(); 
    	$filename = basename($source);
    	$arr_temp = explode('.', $filename);
    	$file_type = array_pop($arr_temp);
    	$path = dirname($source).'/'.implode('.', $arr_temp);
    	$this->setCutType($cutType);
    	if($val){
    		if(is_array($val[0])){
	    		foreach ($val as $key => $value) 
	    		{
		    		 $this -> setSrcImg($source);
		    		 $suffix = "_{$value[0]}x{$value[1]}";
		    		if(preg_match('|(_[1-9][0-9]*x[1-9][0-9]*)$|', $path))
					{
						$dst_img = preg_replace('|(_[1-9][0-9]*x[1-9][0-9]*)$|', $suffix, $path).".{$file_type}";
					}
					else
					{
					 	$dst_img ="{$path}{$suffix}.{$file_type}" ;
					}
	    			$this -> setDstImg($dst_img);
	    			$arr_dst_img[$suffix] =$dst_img;
	    			$this -> createImg($value[0],$value[1]);	
	    		}
    		}else{
    			$this -> setSrcImg($source);
	    	 	$suffix = "_{$val[0]}x{$val[1]}";
    			if(preg_match('|(_[1-9][0-9]*x[1-9][0-9]*)$|', $path))
				{
					$dst_img = preg_replace('|(_[1-9][0-9]*x[1-9][0-9]*)$|', $suffix, $path).".{$file_type}";
				}else
				{
					 $dst_img ="{$path}{$suffix}.{$file_type}" ;
				}
	    		$this -> setDstImg($dst_img);
	    		$this -> createImg($val[0],$val[1]);	
	    		$arr_dst_img[$suffix]=$dst_img;
	    		
	    	}
    	}
    	return $arr_dst_img;
    }
}

/*
{
 //使用方法零：给图片打水印  
 $t = new ThumbHandler;
 $getImg='../tmp/new_test.jpg';
 $t->setSrcImg("../image/test.jpg");//原始图片路径
 $t->setMaskImg("../image/1_small.png");//水印图片路径
 $t->setDstImg($getImg);//生成的图片路径
 $t->setMaskPosition(1);//值为 1、2、3、4分别对应图片的4个角.
 {//设置水印的位置通过坐标指定
  //$t->setMaskOffsetX($_GET['x']);
  //$t->setMaskOffsetY($_GET['y']);
 }
 $t->setMaskImgPct($_GET['flag']);//透明度$_GET['flag']=0-100为不同程度的分辨率.
 $t->setDstImgBorder(4,"#dddddd");
 $t->createImg(300,250);// 指定缩放比例
 $t->setDstImgBorder(4,"#dddddd");//设置边框
 $t->dispalyImg($getImg,'',true);
}
*/
/*{
 //使用方法一：在图片上输出文字 用于打水印
 $t = new ThumbHandler();
  $getImg='../tmp/new_test.jpg';
 // 基本使用
 $t->setSrcImg("../image/test.jpg");
 $t->setMaskWord("test");//字的内容
 $t->setMaskFont("../lib/汉真广标艺术字体.ttf");//设置水印字体,网上下载字体库
 $t->setMaskFontSize(10);//设置水印字体大小
 $t->setMaskFontColor("#FF80FF");//设置水印字体颜色
 $t->setMaskPosition(4);//字的位置
 $t->setDstImgBorder(10,"#dddddd");//字体边框
  $t->setDstImg($getImg);//生成的图片路径
 // 指定缩放比例
 $t->createImg(50);
 $t->dispalyImg($getImg,'',true);
}
*/
/*
{//使用方法二：带中文水印的打法。
 $t = new ThumbHandler();
 $getImg='../tmp/new_test.jpg';
 $t->setSrcImg("../image/test.jpg");
 $t->setMaskFont("../lib/汉真广标艺术字体.ttf");
 $t->setMaskFontSize(12);
 $t->setMaskFontColor("#ffffff");
 $t->setMaskTxtPct(20);
 $t->setDstImgBorder(5,"#dddddd");
 $text = "中文";
 $str = mb_convert_encoding($text, "UTF-8", "gb2312");
 $t->setMaskWord($str);
 $t->setMaskWord(" test");
 $t->setDstImg($getImg);//生成的图片路径
 $t->createImg(50);// 指定固定宽高
 $t->dispalyImg($getImg,'',true);
}*/

/*
{//使用方法三：剪切图片  
 $t = new ThumbHandler();
 $getImg='../tmp/new_test.jpg';
 $t->setSrcImg("../image/test.jpg");
 $t->setCutType(2);//0 按比例放大缩小图片 ，1自动剪切，2:手工剪切图片
 $t->setSrcCutPosition(550, 400);//设置切割起始位置
 $t->setRectangleCut(150,100);//设置切割图片 宽高 
 $t->setDstImg($getImg);//生成的图片路径
 $t->createImg(100,100);// 指定固定宽高
 $t->dispalyImg($getImg,'',true);
}
*/
/*
{//使用方法四：剪切图片 
 $t = new ThumbHandler();
 $getImg='../tmp/new_test.jpg';//生成图片
 $t->setSrcImg("../image/test.jpg");//设置图片
 $t->setCutType(1);
 //0 按比例放大缩小图片（createImg函数无效） ，
 //1自动剪切（按createImg函数指定参数修改图片 宽、高），
 //2:手工剪切图片
 $t->setSrcCutPosition(550, 400);//设置切割起始位置
 $t->setRectangleCut(150,100);//设置切割图片 宽高 
 $t->setDstImg($getImg);//生成的图片路径
 $t->createImg(100,100);// 指定固定宽高
 $t->dispalyImg($getImg,'',true);
}
*/
//$t = new Image();
//
//$t = new Image();
//
//$t -> reallizebox('flower2.jpg',
//                    array(400,560)
//                  );
?>
