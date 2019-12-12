Simple docker pack with symfony cib6randomizer app

based https://github.com/eko/docker-symfony

To intall and use u need:

Install docker

`curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -`

`sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu bionic stable"`

`sudo apt update`

`sudo apt install docker-ce`

Install docker - compose

`sudo curl -L "https://github.com/docker/compose/releases/download/1.25.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose`

`sudo chmod +x /usr/local/bin/docker-compose`

`sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose`

Test the installation.

`docker-compose --version`

U can see 

`docker-compose version 1.25.0, build 1110ad01`

Go to repo folder and:

`docker-compose up`
