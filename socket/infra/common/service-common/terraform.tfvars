terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform/ecs/service-common?ref=v0.1-32-g233152f"
  }

  dependencies {
    paths = ["../ecs-cluster"]
  }

  include {
    path = "${find_in_parent_folders()}"
  }
}
