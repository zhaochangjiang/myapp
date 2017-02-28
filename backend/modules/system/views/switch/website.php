<style type="text/css">
    .margin-bottom-90{ height: 90px;}
    .nodeline{
        position:absolute;
        width:100px;
        height:100px;
        border-bottom:1px solid #cccccc;
    }
    .nodelilineup{border-bottom:1px solid #cccccc;  height: 44px;  position:absolute;


    }
    .nodelinedown{
        border-bottom:1px solid #cccccc; height: 44px; position:absolute;

    }
</style>
<div class="col-md-12 text-center">
    <?php
    $data = $this->data['nodeList'];

    foreach ($data as $key => $value) {
        $string = '<div class="row">';

        $length = ceil(12 / $value['size']);
        $index = 0;
        $maxLength = count($value['data']);
        foreach ($value['data'] as $val) {
            $index++;
            $buttonType = 'btn-default';
            if ($val['status'] === 'on') {
                $buttonType = 'btn-success';
            }
            $string.= '<div class="col-md-' . $length . '"><a  maxCount="'.$maxLength.'" ind="' . $index . '" uppid="' . $val['heigher_level_id'] . '" uid="' . $val['id'] . '" class="nodeLi btn ' . $buttonType . '">' . $val['name'] . '</a></div>';
        }
        $string.='</div>';
        $string.='<div class="margin-bottom-90"><div class="nodelilineup"></div>';
        $string.='<div class="nodelilinedown"></div>';
        $string.='</div>';
        echo $string;
    }
//print_r($data);
    ?>
    <script type="text/javascript">
        $(function () {
            var offsetList = new Array();
            $(".nodeLi").each(function () {
                if (typeof ($(this).attr('uppid')) !== 'undefined' && $(this).attr('uppid') !== '') {
                    var uppid = $(this).attr("uppid");
                    var uid = $(this).attr('uid');
                    var sUid = 's_' + uid;
                    $("body").append("<div id='" + sUid + "' class='nodeline'></div>");
                    if (typeof (offsetList[uid]) === 'undefined') {
                        offsetList[uid] = $('[uid="' + uppid + '"]').offset();
                    }
                    thisOffset = offsetList[uid];
                    if (typeof (offsetList[uppid]) === 'undefined') {
                        offsetList[uppid] = $('[uid="' + uppid + '"]').offset();
                    }
                    if (parseInt($(this).attr('ind'))===1) {
                        var thisOffset = $(this).offset();
                        var upNodeLiUp = $(this).parents(".row").prev(".margin-bottom-90").find(".nodelilineup");
                        var w = $(this).parent().width()*(parseInt($(this).attr('maxCount'))-1);
                        
                        upNodeLiUp.css({left: thisOffset.left-$("aside.left-side").width()+$(this).width()/2,width:w});
                    }
                }
            });
            console.log(offsetList);
        });

    </script>
</div>

