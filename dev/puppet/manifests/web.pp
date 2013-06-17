class web {
  class {'jazzee': }
  case $operatingsystem {
    centos, redhat: { 
      $enhancers = [ "screen", "emacs", "vim-enhanced" ]
      $firewall = ["iptables", "ip6tables"]
      file { "/var/log/httpd":
        ensure => directory,
        group => 'apache',
        mode => '0755',
        recurse => true, 
        require => [Group['apache']]
      }
      file { "/var/log/httpd/error_log":
        ensure => present,
        group => 'apache',
        mode => '0644',
        require => File['/var/log/httpd']
      }
      group { 'apache':
        ensure => present,
        require => Class['jazzee']
      }
      user { 'vagrant':
        ensure  => present,
        groups => ["apache"],
        require => [Group['apache']]
      }
      file { "/etc/yum/pluginconf.d/fastestmirror.conf":
        content => "[main]\nenabled=0"
      }
      Package { require => File['/etc/yum/pluginconf.d/fastestmirror.conf'] }
    }
  }

  package { $enhancers: ensure => "latest" }

  service {$firewall: ensure => stopped }
}

class {'web': }
