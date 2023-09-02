terragrunt = {
  remote_state {
    backend = "s3"

    config {
      bucket  = "flb-infra"
      key     = "terraform/fleetbase/docker-cluster/${path_relative_to_include()}/terraform.tfstate"
      region  = "ap-southeast-1"
      profile = "fleetbase"

      skip_requesting_account_id  = true
      skip_get_ec2_platforms      = true
      skip_metadata_api_check     = true
      skip_region_validation      = true
      skip_credentials_validation = true
    }
  }

  terraform {
    extra_arguments "tfvars" {
      commands  = ["${get_terraform_commands_that_need_vars()}"]
      arguments = ["-var", "root=${get_parent_tfvars_dir()}"]
    }

    extra_arguments "vars" {
      commands = ["${get_terraform_commands_that_need_vars()}"]

      required_var_files = [
        "${get_parent_tfvars_dir()}/common.tfvars",
        "${get_tfvars_dir()}/../environment.tfvars",
      ]
    }

    extra_arguments "auto_approve" {
      commands = [
        "apply",
      ]

      arguments = [
        "-auto-approve",
      ]
    }
  }
}
