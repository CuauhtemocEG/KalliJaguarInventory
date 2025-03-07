<p class="has-text-left  pb-4">
	<a href="#" class="btn btn-danger">Regresar</a>
</p>

<script type="text/javascript">
    let btn_back = document.querySelector(".btn-back");

    btn_back.addEventListener('click', function(e){
        e.preventDefault();
        window.history.back();
    });
</script>