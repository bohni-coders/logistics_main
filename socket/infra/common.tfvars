project = "flb-socket"

aws_profile = "fleetbase"

ec2_key_name = "ecs"

kms = false

kms_common = false

services = ["app"]

environments = ["staging", "production", "qa", "dev"]

load_balancer_listener_port = 8000

deploy_user = "gitlab-flb-socket"

gitlab_project_id = 13

# we are reusing the load balancer from service

load_balancer_name = "flb-service"
