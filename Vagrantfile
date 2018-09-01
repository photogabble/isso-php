# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.vm.hostname = "isso-dev.local"
  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.network "forwarded_port", guest: 3306, host: 3333
  config.vm.synced_folder ".", "/vagrant_data", id: "vagrant-root",
    owner: "vagrant",
    group: "www-data",
    mount_options: ["dmode=775,fmode=664"]
  config.vm.provider "virtualbox" do |vb|
    # Setup VM with 1 CPU and 512MB of RAM, this should be
    # more than enough for development.
    vb.memory = "1024"
    vb.cpus = "1"
  end
  config.vm.provision "shell", path: "./.vagrant/bootstrap.sh"
end