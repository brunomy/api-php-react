
<script>

    var owlcss = document.createElement('link');
    owlcss.setAttribute('rel', 'stylesheet');
    owlcss.setAttribute('type', 'text/css');
    owlcss.setAttribute('href', 'plugins/izimodal/izimodal.css');
    document.getElementsByTagName("head")[0].appendChild(owlcss);

    $(function(){

        $(".modais").iziModal({
            history: false,
            width: 1200,
            iframeHeight: 800,
            iframe : true,
            fullscreen: false,
            headerColor: '#000000',
            group: 'group1',
            loop: true
        });

    })

</script>

<script src="plugins/izimodal/izimodal.js"></script>
