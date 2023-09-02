environment = "common"

ecs_cluster_spot = false # socket-cluster is used by production, so we can't use spot instances

addresses = ["socket.fleetbase.io", "socket.qa.fleetbase.io", "socket.staging.fleetbase.io", "socket.development.fleetbase.io"]
