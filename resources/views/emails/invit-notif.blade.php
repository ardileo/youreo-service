<h3>Halo, {{ $target }} !</h3>
<p>
    Kamu telah diundang oleh <b>{{$invitor}}</b> sebagai <b><i>contributor</i></b> pada event :<br/>
    <b>{{$event['title']}}</b> yang akan dilaksanakan pada  <br/>
    {{\Carbon\Carbon::parse($event['date_start'])->format("M j, Y -  H:s") }} WIB
</p>
<p>
    Apabila Anda ingin berpartisipasi di acara ini, <br/>Anda bisa langsung konfirmasi melalui link berikut:
</p>
<a href="{{ $cta_link }}">{{ $cta_link }}</a>
<p>
    Terima kasih <br/><br/>
    ============================<br/>
    <b>YOUREO - <i>Your Best Event Partner</i></b><br/>
    ============================
</p>
