<?php
$urlString = $this->createUrl(array(
    $this->controllerObject->controllerString,
    'getchildpermit',
    $this->controllerObject->moduleString));
?>

<script type="text/javascript">
    function changeSelectedUid(o) {
        //   loadShow.loading();
        o.parents(".selectuppid").nextAll(".selectuppid").remove();
        $.post(o.attr("loadUrl"), {id: o.val()}, function (r) {

            data = eval("(" + r + ")");
            if (data.length < 1) {
                return;
            }
            var s = '<option value="">--请选择--</option>';
            for (var p in data) {
                s += '<option  value="' + data[p].id + '">' + data[p].name + '</option>';
            }
            o.parents('.selectuppid').after('<div class="col-lg-2 selectuppid"> <select class="form-control" name="uppid[]"  loadUrl="' + '<?php
                    echo $urlString;
                    ?>' + '" onchange="changeSelectedUid($(this));">' + s + '</select></div>');
            //       loadShow.loadClose();
        });
    }
</script>
<div class="col-lg-10">
    <div class="row">
        <?php
        $count = count($this->data['data']);
        foreach ($this->data['data'] as $value) {
            $i++;

            $optionString = '';

            foreach ($value['permitList'] as $k => $v) {
                $selectString = '';
                $nameFlag = true;
                switch ($this->controllerObject->data['doType']) {
                    case 'update':
                        if ($v['id'] == $value['nowId'] && $count == $i) {
                            $nameFlag = false;
                            continue;
                        }
                        if ($v['id'] == $value['nowId'] && $count != $i) {
                            $selectString = ' selected="selected"';
                        }
                        break;
                    default:
                        if ($v['id'] == $value['nowId']) {
                            $selectString = ' selected="selected"';
                        }
                        break;
                }
                if ($nameFlag) {
                    $optionString .= '<option ' . $selectString . ' value="' . $v['id'] . '">' . $v['name'] . '</option>';
                }
            }
            if (!empty($optionString)) {
                echo '<div class="col-lg-2 selectuppid">
                                <select class="form-control" name="uppid[]" loadUrl="' . $urlString . '" onchange="changeSelectedUid($(this));">
                                <option value="">--请选择--</option>
                                ' . $optionString . '</select>
                        </div>';
            }
        }
        ?>
    </div>
</div>




