Veewee::Session.declare({
  :cpu_count => '1',
  :memory_size=> '512',
  :disk_size => '10140',
  :disk_format => 'VDI',
  :hostiocache => 'off',
  :os_type_id => 'RedHat_64',
  :iso_file => "OracleLinux-R6-U3-Server-x86_64-dvd.iso",
  :iso_src => "http://mirrors.wimmekes.net/pub/OracleLinux/OL6/U3/x86_64/OracleLinux-R6-U3-Server-x86_64-dvd.iso",
  :iso_md5 => "7daae91cc0437f6a98a4359ad9706d678a9f19de",
  :iso_download_timeout => 1000,
  :boot_wait => "10",
  :boot_cmd_sequence => [
    '<Tab> text ks=http://%IP%:%PORT%/ks.cfg<Enter>'
  ],
  :kickstart_port => "7122",
  :kickstart_timeout => 10000,
  :kickstart_file => "ks.cfg",
  :ssh_login_timeout => "10000",
  :ssh_user => "veewee",
  :ssh_password => "veewee",
  :ssh_key => "",
  :ssh_host_port => "7222",
  :ssh_guest_port => "22",
  :sudo_cmd => "echo '%p'|sudo -S sh '%f'",
  :shutdown_cmd => "/sbin/halt -h -p",
  :postinstall_files => [
    "base.sh",
    "ruby.sh",
    "puppet.sh",
    "vagrant.sh",
    "virtualbox.sh",
    "rpms.sh",
    "cleanup.sh",
    "zerodisk.sh"
  ],
  :postinstall_timeout => 10000
})
