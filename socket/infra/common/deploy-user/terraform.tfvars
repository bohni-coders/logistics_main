terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform//deploy-user?ref=v0.1-23-g7635de9"
  }

  dependencies {}

  include {
    path = "${find_in_parent_folders()}"
  }
}
