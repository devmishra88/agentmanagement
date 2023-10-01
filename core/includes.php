<script>
	var NoAccessMSG = "<?php echo $NoAccessMessage;?>";
</script>
<?php
if($NoAccess == 1)
{
	?>
	<script type="text/javascript">
		AttachEvent(window,"onload",NoAccess);
	</script>
	<?php
}
?>