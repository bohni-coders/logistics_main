terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform/ecs/service?ref=v0.1-38-gb664f81"
  }

  dependencies {
    paths = ["../service-common"]
  }

  include {
    path = "${find_in_parent_folders()}"
  }
}

service_name = "app"

container_port = 80
