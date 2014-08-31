multikanban
===========

A simple kanban for multiple personal projects.


# Request & Response Examples

## API Resources

  - [GET /users](#get-users)
  - [GET /users/[id]]
  - [GET /users/[id]/kanbans]
  - [GET /users/[id]/kanbans/[id]/
  - [GET /users/[id]/kanbans/[id]/tasks
  - [POST /users/[id]]

### GET /users

Example: http://multikanban.com/api/users.json

Response body:

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
