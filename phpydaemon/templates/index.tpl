% include templates/header

<div class="page-header">
  <h1>phpydaemon status</h1>
</div>
<div id="phpydaemon-stats-container"></div>

<script type="text/javascript">
  function phpydaemonStatsPoll() {
    $('#phpydaemon-stats-container').load('/status');
    setTimeout('phpydaemonStatsPoll()', 1000);
  }
  $(document).ready(function() {
    phpydaemonStatsPoll();
  });
</script>

% include templates/footer
