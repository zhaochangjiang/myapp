<?php

use framework\App;
?>
<aside class="left-side sidebar-offcanvas">
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?php echo $this->data['avater'] ?>" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p><?php echo $this->data['session']['username'] ?></p>
                <a href="<?php
                echo $this->createUrl(
                        array('Passport','logout'),
                         null, App::base()->params['domain']['web']);
                ?>"><i class="fa fa-circle text-success"></i> 在线</a>
            </div>
        </div>
        <!-- search form
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
                <span class="input-group-btn">
                    <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
        /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->

        <ul class="sidebar-menu">
            <?php
            foreach ($this->permitList['left'] as $k => $v) {
                $moduleAction = $this->getAdminModuleActionArray($v);
               
                //  print_r($moduleAction);
                if (empty($v['childList'])) {//如果菜单没有子菜单
                    echo ' <li class="' . ($v['active'] ? 'active ' : '') . '"> 
                        <a href="' . (!empty($moduleAction) ? $this->createUrl($moduleAction) : "javascript:void(0);") . '">
                           ' . (empty($v['csscode']) ? '<i class="fa fa-gear"></i>' : ' <i class="fa ' . $v['csscode'] . '"></i>') . ' <span>' . $v['name'] . ' </span>
                        </a>
                    </li>';
                    continue;
                }
                if ($v['active']) {
                    echo ' <li class=" treeview active "> 
                        <a href="' . (!empty($moduleAction) ? $this->createUrl($moduleAction) : "javascript:void(0);") . '">
                           ' . (empty($v['csscode']) ? '<i class="fa fa-gear"></i>' : ' <i class="fa ' . $v['csscode'] . '"></i>') . ' <span>' . $v['name'] . ' </span>
                        <i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu" style="display: block;">';
                } else {
                    echo ' <li class=" treeview"> 
                        <a href="' . (!empty($moduleAction) ? $this->createUrl($moduleAction) : "javascript:void(0);") . '">
                           ' . (empty($v['csscode']) ? '<i class="fa fa-gear"></i>' : ' <i class="fa ' . $v['csscode'] . '"></i>') . ' <span>' . $v['name'] . ' </span>
                        <i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu" style="display: none;">';
                }

                foreach ($v['childList'] as $key => $value) {

                    $moduleAction = $this->getAdminModuleActionArray($value);
                  //  print_r($moduleAction);
                    echo ' <li class="' . (($value['active']) ? 'active' : '') . '"><a href="' . (!empty($moduleAction) ? $this->createUrl($moduleAction) : "javascript:void(0);") . '" style="margin-left: 10px;"><i class="fa fa-angle-double-right"></i> ' . $value['name'] . '</a></li>';
                }
                echo ' </ul></li>';
            }
            ?>
        </ul>

        <div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-envelope-o"></i> 信息提示</h4>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer clearfix">

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

    </section>
    <script type="text/javascript">
//        var loadShow = {
//          
//            loading: function () {
//            },
//            loadClose: function () {
//            }
//        };

        function showerror(message) {
            $('#compose-modal .modal-body').html(message);
            $('#compose-modal').modal('show');
        }
        function doAjaxDelete(u1, u2) {
            if (confirm("你确定要删除该信息？")) {
                $.post(u1, null, function (r) {
                    if (r === 'ok') {
                        location.href = u2;
                        return;
                    }
                    $('#compose-modal .modal-body').html(r);
                    $('#compose-modal').modal('show');

                });
            }
            return;
        }

    </script>
</aside>