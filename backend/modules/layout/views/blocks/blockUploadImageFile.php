<?php
if (empty($this->params['paramName'])) {
    $this->params['paramName'] = 'filename';
}
if (empty($this->params['minFileCount'])) {
    $this->params['minFileCount'] = 0;
}
?>
<div id="uploadfile_<?php echo $this->params['paramName']; ?>">
    <?php // echo $this->getJonUploadParam(); ?>
    <input id="file-<?php echo $this->params['paramName']; ?>"
           data-upload-url="<?php echo $this->params['uploadUrl']; ?>" name="<?php echo $this->params['paramName']; ?>"
           class="file" type="file" multiple data-min-file-count="<?php echo $this->params['minFileCount']; ?>"
           data-max-file-count="<?php echo $this->params['maxFileCount']; ?>">
    <script>
        $("#file-<?php echo $this->params['paramName']; ?>").fileinput(<?php echo $this->getJonUploadParam(); ?>);
    </script>
</div>
