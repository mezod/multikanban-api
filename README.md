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
      "username" : "mezod",
      "password" : "my_password",
    }
  
#### GET /users

Get all users.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberKanbans": "7",
    },
    {
        "id": "2",
        "nickname": "cowboycoder",
        "registered": "31/08/2014",
        "numberKanbans": "3",
    },
    {
        "id": "3",
        "nickname": "gravitysrainbow",
        "registered": "31/08/2014",
        "numberKanbans": "4",
    }

#### <a name="get-user"></a>GET /users/:id

Get user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberKanbans": "7",
    }
    
#### <a name="put-user"></a>PUT /users/:id

Update user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "registered": "31/08/2014",
        "numberKanbans": "8",
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

### KANBANS

#### <a name="post-kanbans"></a>POST /users/:id/kanbans

Create a kanban for user :id

    {
        "title": "Summer trip",
        "position": "0"
    }

#### <a name="get-kanbans"></a>GET /users/:id/kanbans

Get all kanbans from user :id

    {
        "title": "Summer trip",
        "dateCreated": "01/09/2014",
        "lastEdited": "01/09/2014",
        "position": "0",
        "numberTasks": "3",
        "numberCompletedTasks": "1"
    },
    {
        "title": "Personal blog",
        "dateCreated": "31/08/2014",
        "lastEdited": "01/09/2014",
        "position": "1",
        "numberTasks": "13",
        "numberCompletedTasks": "4"
    },
    {
        "title": "Thesis",
        "dateCreated": "31/09/2014",
        "lastEdited": "01/09/2014",
        "position": "2",
        "numberTasks": "23",
        "numberCompletedTasks": "12"
    }

#### <a name="get-kanban"></a>GET /users/:id/kanbans/:kanban_id

Get kanban :kanban_id from user :id

    {
        "title": "Thesis",
        "dateCreated": "31/09/2014",
        "lastEdited": "01/09/2014",
        "position": "2",
        "numberTasks": "23",
        "numberCompletedTasks": "12"
    }

#### <a name="put-kanban"></a>PUT /users/:id/kanbans/:kanban_id

Update kanban :kanban_id from user :id

Request:

    {
        "title": "Master's Thesis",
        "position": "2",
    }

#### <a name="delete-kanbans"></a>DELETE /users/:id/kanbans

Delete all kanbans from user :id

#### <a name="delete-kanban"></a>DELETE /users/:id/kanbans/:kanban_id

Delete kanban :kanban_id from user :id

    {
        "title": "Thesis",
        "dateCreated": "31/09/2014",
        "lastEdited": "01/09/2014",
        "position": "2",
        "numberTasks": "23",
        "numberCompletedTasks": "12"
    }

### TASKS

#### <a name="post-tasks"></a>POST /users/:id/kanbans/:kanban_id/tasks

Create a task in kanban :kanban_id of user :id

    {
      "text": "Write the abstract",
      "position": "0",
      "column": "backlog"
    }

#### <a name="get-tasks"></a>GET /users/:id/kanbans/:kanban_id/tasks

Get all tasks from kanban :kanban_id from user :id

    {
      "text": "write the abstract",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "backlog"
    },
    {
      "text": "meet supervisor",
      "dateCreated": "31/08/2014",
      "position": "1",
      "column": "backlog"
    },
    {
      "text": "find a topic",
      "dateCreated": "31/08/2014",
      "dateEnd": "01/09/2014"
      "position": "0",
      "column": "done"
    },
    {
      "text": "install latex",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "to do"
    },
    {
      "text": "book lab",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "doing"
    },
    {
      "text": "book lab",
      "dateCreated": "17/08/2014",
      "dateEnd": "24/08/2014"
      "position": "0",
      "column": "archive"
    }
    

#### <a name="get-completed-tasks"></a>GET /users/:id/completedtasks

Get all completed tasks from user :id

    {
      "text": "find a topic",
      "dateCreated": "31/08/2014",
      "dateEnd": "01/09/2014"
      "position": "0",
      "column": "done"
    },
    {
      "text": "book lab",
      "dateCreated": "17/08/2014",
      "dateEnd": "24/08/2014"
      "position": "0",
      "column": "archive"
    }

#### <a name="put-task"></a>PUT /users/:id/kanbans/:kanban_id/tasks/:task_id

Update task :task_id from kanban :kanban_id from user :id

    {
      "text": "write the abstract",
      "dateCreated": "31/08/2014",
      "position": "1",
      "column": "to do"
    }

#### <a name="delete-tasks"></a>DELETE /users/:id/tasks

Delete all tasks from user :id

#### <a name="delete-task"></a>DELETE /users/:id/kanbans/:kanban_id/tasks/:task_id

Delete task :task_id from kanban :kanban_id from user :id

    {
      "text": "meet supervisor",
      "dateCreated": "31/08/2014",
      "position": "1",
      "column": "backlog"
    }


# MongoDB Document Structure Example

    {
     numberUsers: "1",
     numberKanbans: "2",
     numberTasks: "123",
     numberCompletedTasks: "43",
     users: [
              {
                id: "1",
                nickname: "mezod",
                email: "mezod@me.zod",
                password: "my_password",
                registered: "31/08/2014",
                numberKanbans: "2",
                numberTasks: "123",
                numberCompletedTasks: "43",
                kanbans: [
                            {
                              id: "1",
                              title: "Summer trip",
                              dateCreated: "31/08/2014",
                              lastEdited: "01/09/2014",
                              position: "1",
                              numberTasks: "32",
                              numberCompletedTasks: "12",
                              tasks: [
                                        {
                                          text: "book flight",
                                          dateCreated: "31/08/2014",
                                          dateCompleted: "31/08/2014",
                                          position: "1",
                                          column: "done"
                                        }
                                    ]
                            },
                            {
                              id: "2",
                              title: "personal blog",
                              dateCreated: "31/08/2014",
                              lastEdited: "31/08/2014",
                              position: "2",
                              numberTasks: "56",
                              numberCompletedTasks: "43",
                              tasks: [
                                        {
                                          text: "write weekly post",
                                          dateCreated: "31/08/2014",
                                          dateCompleted: "",
                                          position: "1",
                                          column: "to do"
                                        }
                                    ]
                            }
                        ]
              },
            ]
    }


# UML Class Diagram (SQL)

![](http://imgur.com/wbtcdSZ)
