% for queueId in data['stats']:

  <h3>
   {{queueId}}
   <small> - {{data['paralell'][queueId]}} paralell workers</small>
  </h3>
  <div class="queue-container" style="margin-left: 20px">
    <p class="stats">
      Running: <span class="badge badge-success">{{data['stats'][queueId]['running']}}</span>
      Queued: <span class="badge badge-important">{{data['stats'][queueId]['queued']}}</span>
      Done: <span class="badge">{{data['stats'][queueId]['done']}}</span>
    </p>
    % if data['jobs'].has_key(queueId) and data['jobs'][queueId]:
      <table class="jobs table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Method</th>
            <th>Args</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          % for job in data['jobs'][queueId]:
          <tr>
            <td>{{job.id}}</td>
            <td>{{job.method}}</td>
            <td>
              {{job.args}}
            </td>
            <td>{{job.status}}</td>
          </tr>
          % end
        </tbody>
      </table>
    % else:
      <div class="alert alert-info no-jobs">No running jobs</div>
    % end
  </div>

% end
