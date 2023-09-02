terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform//ecs/ecr?ref=v0.1-4-g498542d"
  }

  dependencies {
    paths = ["../deploy-user"]
  }

  include {
    path = "${find_in_parent_folders()}"
  }
}
