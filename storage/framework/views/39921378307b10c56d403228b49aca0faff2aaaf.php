<h3>Halo, <?php echo e($target); ?> !</h3>
<p>
    Kamu telah diundang oleh <b><?php echo e($invitor); ?></b> sebagai <b><i>contributor</i></b> pada event :<br/>
    <b><?php echo e($event['title']); ?></b> yang akan dilaksanakan pada  <br/>
    <?php echo e(\Carbon\Carbon::parse($event['date_start'])->format("M j, Y -  H:s")); ?> WIB
</p>
<p>
    Apabila Anda ingin berpartisipasi di acara ini, <br/>Anda bisa langsung konfirmasi melalui link berikut:
</p>
<a href="<?php echo e($cta_link); ?>"><?php echo e($cta_link); ?></a>
<p>
    Terima kasih <br/><br/>
    ============================<br/>
    <b>YOUREO - <i>Your Best Event Partner</i></b><br/>
    ============================
</p>
<?php /**PATH C:\laragon\www\youreoAPI\resources\views/emails/invit-notif.blade.php ENDPATH**/ ?>