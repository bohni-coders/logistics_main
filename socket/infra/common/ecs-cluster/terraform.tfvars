terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform/ecs/ecs-cluster?ref=v0.1-21-g5bb90a1"
  }

  dependencies {}

  include {
    path = "${find_in_parent_folders()}"
  }
}
