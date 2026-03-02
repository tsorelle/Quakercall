<footer id='site-footer' class="footer mt-auto py-3">
    <div class="container">
        <div class="d-flex justify-content-end" style="float: right">

            <?php
//                /** @var  $editorsignedin */
//                if ($editorsignedin) {
                    /** @noinspection HtmlUnknownTarget */
//                    print '<div class="me-5"><a href="\song\new" class="mr-5">New Song</a></div>';
//                }
                /** @var $signin */
                print $signin;
           ?>
        </div>
        <span class="d-flex justify-content-start"><a href="/contact">Contact us</a> <span style="padding-left: 1rem;padding-right: 1rem" >|</span> <a href="/privacy">Privacy Policy</a>

        </div>
        <!-- span class="text-muted">Place sticky footer content here.</span -->
    </div>
</footer>
