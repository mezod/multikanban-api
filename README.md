multikanban
===========

A simple kanban for multiple personal projects.


# Request & Response Examples

## API Resources

### STATS

Stores and updates information about stats of the app.

| Endpoint | Description |
| ---- | --------------- |
| [GET /stats](#get-stats) | Get app stats; total number of users, kanban boards, tasks and completed tasks for the app  |
| [GET /users/:id/stats](#get-user-stats) | Get stats for user :id; number of kanban boards, tasks and completed tasks |
| [GET /kanbans/:kanban_id/stats](#get-kanban-stats) | Get stats for kanban :kanban_id; number of tasks and completed tasks |

### USERS

Stores and updates information about users of the app.

| Endpoint | Description |
| ---- | --------------- |
| [POST /users](#post-users) | Create a user |
| [GET /users](#get-users) | Get all users |
| [GET /users/:id](#get-user) | Get user :id data |
| [PUT /users/:id](#put-user) | Update user's data |
| [DELETE /users/:id](#delete-user) | Delete user |
  
### KANBANS

Stores and updates information about users' kanban boards.
  
| Endpoint | Description |
| ---- | --------------- |
| [POST /users/:id/kanbans](#post-kanbans) | Create a kanban for user :id |
| [GET /users/:id/kanbans](#get-kanbans) | Get all kanbans from user :id |
| [GET /users/:id/kanbans/:kanban_id](#get-kanban) | Get kanban :kanban_id from user :id |
| [PUT /users/:id/kanbans/:kanban_id](#put-kanban) | Update kanban :kanban_id from user :id |
| [DELETE /users/:id/kanbans](#delete-kanbans) | Delete all kanbans from user :id |
| [DELETE /users/:id/kanbans/:kanban_id](#delete-kanban) | Delete kanban :kanban_id from user :id |

### TASKS

Stores and updates information about users' kanban board's tasks.

| Endpoint | Description |
| ---- | --------------- |
| [POST /users/:id/kanbans/:kanban_id/tasks](#post-tasks) | Create a task in kanban :kanban_id of user :id |
| [GET /users/:id/kanbans/:kanban_id/tasks](#get-tasks) | Get all tasks from kanban :kanban_id from user :id |
| [GET /users/:id/completedtasks](#get-completed-tasks) | Get all completed tasks from user :id |
| [PUT /users/:id/kanbans/:kanban_id/tasks/:task_id](#put-task) | Update task :task_id from kanban :kanban_id from user :id |
| [DELETE /users/:id/tasks](#delete-tasks) | Delete all tasks from user :id |
| [DELETE /users/:id/kanbans/:kanban_id/tasks/:task_id](#delete-task) | Delete task :task_id from kanban :kanban_id from user :id |

### STATS

#### <a name="get-stats"></a>GET /stats

Get app stats; total number of users, kanban boards, tasks and completed tasks for the app

Response:

  {
      "numberUsers" : "12",
      "numberKanbans" : "67",
      "numberTasks" : "489",
      "numberCompletedTasks": "274"
  }

#### <a name="get-user-stats"></a>GET /users/:id/stats

Get stats for user :id; number of kanban boards, tasks and completed tasks

Response:

  {
      "numberKanbans" : "6",
      "numberTasks" : "86",
      "numberCompletedTasks": "57"
  }

#### <a name="get-kanban-stats"></a>GET /kanbans/:kanban_id/stats

Get stats for kanban :kanban_id; number of tasks and completed tasks

Completed tasks are the sum of the "done" and "archive" columns of the kanban board

Response:

  {
      "numberTasks" : "24",
      "numberCompletedTasks": "11"
  }

### USERS

#### POST /users

Create a new user.

Request:

  {
      "username" : "my_username",
      "password" : "my_password",
      "registered": "01/09/2014",
     
      "numberKanbans" : "0",
      "numberTasks" : "0",
      "numberCompletedTasks": "0"
  }
  


#### GET /users

Get all users.

Response:

    {
        "metadata": {
            "count": 123,
        },
        "results": [
            {
                "id": "1",
                "nickname": "mezod",
                "registered": "31/08/2014",
                "numberkanbans": "7",
            },
            {
                "id": "2",
                "nickname": "cowboycoder",
                "registered": "31/08/2014",
                "numberkanbans": "3",
            },
            {
                "id": "3",
                "nickname": "gravitysrainbow",
                "registered": "31/08/2014",
                "numberkanbans": "4",
            },

        ]
    }

#### <a name="get-user"></a>GET /users/:id

Get user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberkanbans": "7",
    }
    
#### <a name="put-user"></a>PUT /users/:id

Update user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberkanbans": "8",
    }
    
#### <a name="delete-user"></a>DELETE /users/:id

Delete user :id.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberkanbans": "7",
    }
