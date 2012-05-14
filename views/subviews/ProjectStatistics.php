<h4>Projektstatistiken</h4>
<ul>
    <li><?= Plant::count() ?> Anlage(n)</li>
    <li><?= humanNumber(Statistics::PeakOverall(), "W") ?><sub>peak</sub> Leistung</li>
    <li><?= humanNumber(Statistics::PowerToday(), "Wh") ?> Heute erzeugt</li>
    <li><?= humanNumber(Statistics::PowerOverall(), "Wh") ?> insgesamt erzeugt</li>
</ul>
