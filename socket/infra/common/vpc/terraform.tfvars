terragrunt = {
  terraform {
    source = "git::ssh://git@gitlab.fleetbase.io/DevOps/infra.git//terraform//vpc?ref=v0.1-28-gb6a2161"
  }

  dependencies {}

  include {
    path = "${find_in_parent_folders()}"
  }
}
