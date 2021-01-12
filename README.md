## Introduction

This repository contains the source code for the 'which 3 birdies' micro-site.
The site uses a mix of HTML, JavaScript and PHP.

## Deployment

### Prerequisites

This site is deployed using Docker so you will need 
[Docker Desktop](https://www.docker.com/products/docker-desktop) installed.
Note that as of Jan 2021, our VM infrastructure does not support docker
desktop on our VMs. Instead, this will need to be installed on a physical 
machine.

The [Azure CLI](https://docs.microsoft.com/en-us/cli/azure/install-azure-cli)
is required in order to authenticate with Azure and run the necessary commands.

### Deployment process

In order to deploy the application, we need to build a docker image using
the latest code, then push it to Azure.

Log in to the Azure portal and open the 'Container registry' named microsites.
This is the location we'll be pushing the image to.

In the 'Access keys' blade, note the Admin username and password. These 
will be needed for the login step below.

#### Login to the container repository

Use a command prompt to execute
`docker login microsites.azurecr.io`
If this is your first time logging in, you'll need to enter the admin
username and password from the container registry access keys page in the 
Azure portal.

#### Build the image

Navigate to the source directory containing the Dockerfile and execute 
`docker build -t [image-name] -f Dockerfile .`

Note - 'Dockerfile' is case-sensitive.

There should now be an entry with the given image-name under the 'images' 
section in docker desktop. If there was already an image, it should have
been updated with the new build

#### Tag the image

Before pushing the image, we need to 'tag' it to match the location we're 
pushing to:

`docker tag [image-name] microsites.azurecr.io/w3b`

#### Push the image

Push the image to the container registry.

`docker push microsites.azurecr.io/w3b`

#### Update services

Restart the w3b-test container instance. You can do this via the Azure 
portal, or the Azure CLI using the `az container restart` command.

As w3b-test is pointing to the 'latest' tag, it will automatically 
pick up the new image when it starts up.

Verify that the changes are working as expected on the 
[test site](w3b-test.uksouth.azurecontainer.io).

Finally, restart the 'which3birdies' app service in order to allow
it to pick up the new image as well.

