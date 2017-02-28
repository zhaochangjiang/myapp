<style>
    .trace{
        position:fixed;background:#F1F1F1;z-index:10000;bottom:0;width:100%;border:2px solid #368EE0;color:#333333;
        padding: 10px;
        overflow:scroll;
        height: 200px;
        line-height: 125%;
        font-size: 14px;
    }
    .trace .close{
        float: left;
        position: relative;
        left: -2px;
        top:15px;
    }
    .trace .plus,
    .trace .minus {
        display:inline;
        vertical-align:middle;
        text-align:center;
        border:1px solid #000;
        color:#000;
        font-size:10px;
        line-height:10px;
        margin:0;
        padding:0 1px;
        width:10px;
        height:10px;
    }
    .trace-item {
        cursor: pointer;
        padding: 0.2em;
    }

    .trace-item :hover {
        background: #f0ffff;
    }

    .trace ul li{
        padding: 6px 0;
    }

    .trace .collapsed .minus,
    .trace .expanded .plus,
    .trace .collapsed ul {
        display: none;
    }
</style>


<div id="_system_output"  class="trace">    
    <a href="javascript:;" onclick="close_system_output();" class="close">x</a>
    <div style="overflow:scroll;height: 200px;position: relative;top:15px">
        <div class="expanded">
            <div class="trace-item"><div class="plus">+</div> 
                <div class="minus">–</div> SQL:</div>
            <ul>

                <?php foreach ($object as $v) { ?>
                    <li><?php echo $v; ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="collapsed">
            <div class="trace-item"><div class="plus">+</div> 
                <div class="minus">–</div> GET:</div>
            <?php
            unset($_GET['r']);
            unset($_GET['finalUrl']);
            unset($_GET['actionName']);
            unset($_GET['moduleName']);
            ?>
            <ul>
                <?php foreach (App::base()->request->query as $key => $val) { ?>
                    <li><font color="red"><?php echo $key; ?></font> : <?php echo $val; ?></li>
                <?php } ?>
            </ul>
        </div>

        <div class="collapsed">
            <div class="trace-item"><div class="plus">+</div> 
                <div class="minus">–</div> POST:</div>
            <ul>
                <?php foreach (App::base()->request->data as $key => $val) { ?>
                    <li><font color="red"><?php echo $key; ?></font> : <?php echo $val; ?></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <script>
        /*<![CDATA[*/
        function getClassObj(className, tag) {
            tag = tag || document;
            className = className || '*';
            var findarr = [];
            if (document.getElementsByClassName) {
                return document.getElementsByClassName(className);
            }
            el = document.getElementsByTagName(tag);
            var pattern = new RegExp("(^|\\s)" + className + "(\\s|$)");
            for (var i = 0; i < el.length; i++) {
                if (pattern.test(el[i].className)) {
                    findarr.push(el[i]);
                }
            }
        }

        function close_system_output() {
            var divs = getClassObj('trace');
            for (i = 0; i < divs.length; i++) {
                getClassObj('trace')[i].style.cssText = "display:none";
            }
        }

        var traceReg = new RegExp("(^|\\s)trace-item(\\s|$)");
        var collapsedReg = new RegExp("(^|\\s)collapsed(\\s|$)");

        var e = document.getElementsByTagName("div");
        for (var j = 0, len = e.length; j < len; j++) {
            if (traceReg.test(e[j].className)) {
                e[j].onclick = function() {
                    var trace = this.parentNode;
                    if (collapsedReg.test(trace.className))
                        trace.className = trace.className.replace("collapsed", "expanded");
                    else
                        trace.className = trace.className.replace("expanded", "collapsed");

                }
            }
        }
        /*]]>*/
    </script>