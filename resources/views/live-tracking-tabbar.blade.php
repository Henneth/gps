<ul class="nav nav-tabs">
    <li id="home-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'class="active"' : '');} else{echo 'class="active"';} ?> ><a href="#" data-toggle="tab">Map</a></li>
    @if ($event->event_type == "fixed route")
        <li id="chart" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Elevation Chart</a></li>
    @endif
    <li id="checkpoint-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 3 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Checkpoint Table</a></li>
    <li id="profile-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Participants Selection</a></li>
</ul>
