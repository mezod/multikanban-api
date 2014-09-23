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
| [DELETE /users/:id/kanbans/:kanban_id](#delete-kanban) | Delete kanban :kanban_id from user :id |

### TASKS

Stores and updates information about users' kanban board's tasks.

| Endpoint | Description |
| ---- | --------------- |
| [POST /users/:id/kanbans/:kanban_id/tasks](#post-tasks) | Create a task in kanban :kanban_id of user :id |
| [GET /users/:id/kanbans/:kanban_id/tasks](#get-tasks) | Get all tasks from kanban :kanban_id from user :id |
| [GET /users/:id/completedtasks](#get-completed-tasks) | Get all completed tasks from user :id |
| [PUT /users/:id/kanbans/:kanban_id/tasks/:task_id](#put-task) | Update task :task_id from kanban :kanban_id from user :id |
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
      "email" : "mezod@me.zod"
    }
  
#### GET /users

Get all users.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "email": "mezod@me.zod",
        "registered": "31/08/2014",
    },
    {
        "id": "2",
        "nickname": "cowboycoder",
        "email": "cowboy@cod.er",
        "registered": "31/08/2014",
    },
    {
        "id": "3",
        "nickname": "gravitysrainbow",
        "email": "gravitys@rain.bow",
        "registered": "31/08/2014",
    }

#### <a name="get-user"></a>GET /users/:id

Get user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "email": "mezod@me.zod",
        "registered": "31/08/2014",
    }
    
#### <a name="put-user"></a>PUT /users/:id

Update user :id data.

Response:

    {
        "id": "1",
        "nickname": "mezod",
        "email": "mezod@me.zod",
        "registered": "31/08/2014",
    }
    
#### <a name="delete-user"></a>DELETE /users/:id

Delete user :id.

Response:

No Content

### KANBANS

#### <a name="post-kanbans"></a>POST /users/:id/kanbans

Create a kanban for user :id

    {
        "user_id": "1",
        "title": "Summer trip",
        "position": "0"
    }

#### <a name="get-kanbans"></a>GET /users/:id/kanbans

Get all kanbans from user :id

    {
        "id": "1",
        "user_id": "1",
        "title": "Summer trip",
        "dateCreated": "01/09/2014",
        "lastEdited": "01/09/2014",
        "position": "0",
    },
    {
        "id": "2",
        "user_id": "1",
        "title": "Personal blog",
        "dateCreated": "31/08/2014",
        "lastEdited": "01/09/2014",
        "position": "1",
    },
    {
        "id": "3",
        "user_id": "1",
        "title": "Thesis",
        "dateCreated": "31/09/2014",
        "lastEdited": "01/09/2014",
        "position": "2",
    }

#### <a name="get-kanban"></a>GET /users/:id/kanbans/:kanban_id

Get kanban :kanban_id from user :id

    {
        "id": "3",
        "user_id": "1",
        "title": "Thesis",
        "dateCreated": "31/09/2014",
        "lastEdited": "01/09/2014",
        "position": "2",
    }

#### <a name="put-kanban"></a>PUT /users/:id/kanbans/:kanban_id

Update kanban :kanban_id from user :id

Request:

    {
        "title": "Master's Thesis",
        "position": "2"
    }

#### <a name="delete-kanban"></a>DELETE /users/:id/kanbans/:kanban_id

Delete kanban :kanban_id from user :id

### TASKS

#### <a name="post-tasks"></a>POST /users/:id/kanbans/:kanban_id/tasks

Create a task in kanban :kanban_id of user :id

    {
      "user_id": "1",
      "kanban_id": "3",
      "text": "Write the abstract",
      "position": "0",
      "column": "backlog"
    }

#### <a name="get-tasks"></a>GET /users/:id/kanbans/:kanban_id/tasks

Get all tasks from kanban :kanban_id from user :id

    {
      "id": "1",
      "user_id": "1",
      "kanban_id": "3",
      "text": "write the abstract",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "backlog"
    },
    {
      "id": "2",
      "user_id": "1",
      "kanban_id": "3",
      "text": "meet supervisor",
      "dateCreated": "31/08/2014",
      "position": "1",
      "column": "backlog"
    },
    {
      "id": "3",
      "user_id": "1",
      "kanban_id": "3",
      "text": "find a topic",
      "dateCreated": "31/08/2014",
      "dateCompleted": "01/09/2014"
      "position": "0",
      "column": "done"
    },
    {
      "id": "4",
      "user_id": "1",
      "kanban_id": "3",
      "text": "install latex",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "to do"
    },
    {
      "id": "5",
      "user_id": "1",
      "kanban_id": "3",
      "text": "book lab",
      "dateCreated": "31/08/2014",
      "position": "0",
      "column": "doing"
    },
    {
      "id": "6",
      "user_id": "1",
      "kanban_id": "3",
      "text": "book lab",
      "dateCreated": "17/08/2014",
      "dateCompleted": "24/08/2014"
      "position": "0",
      "column": "archive"
    }
    

#### <a name="get-completed-tasks"></a>GET /users/:id/completedtasks

Get all completed tasks from user :id

     {
      "id": "3",
      "user_id": "1",
      "kanban_id": "3",
      "text": "find a topic",
      "dateCreated": "31/08/2014",
      "dateCompleted": "01/09/2014"
      "position": "0",
      "column": "done"
    },
    {
      "id": "6",
      "user_id": "1",
      "kanban_id": "3",
      "text": "book lab",
      "dateCreated": "17/08/2014",
      "dateCompleted": "24/08/2014"
      "position": "0",
      "column": "archive"
    }

#### <a name="put-task"></a>PUT /users/:id/kanbans/:kanban_id/tasks/:task_id

Update task :task_id from kanban :kanban_id from user :id

    {
      "text": "write the abstract",
      "position": "1",
      "column": "to do"
    },

#### <a name="delete-task"></a>DELETE /users/:id/kanbans/:kanban_id/tasks/:task_id

Delete task :task_id from kanban :kanban_id from user :id

# ERRORS

## 400 validation_error

There was a validation error.

## 400 invalid_body_format

Invalid JSON format sent.

## 404 not_found

Resource not found.

## 409 already_exists

Resource already exists.

# UML Class Diagram (SQL)

![](http://i.imgur.com/dlIVwYB.png)

# Milestones

- POST, GET, PUT, DELETE, PATCH
- Controllers
- Services
- Interfaces
- Providers
- Listeners
- Behat
- Exception handling
- DBAL Annotations

## ERROR HANDLING

- Validation errors
- Invalid JSON errors
- 404 errors

# DISCARDED

## MongoDB Document Structure Example

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

