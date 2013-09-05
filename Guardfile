# A sample Guardfile
# More info at https://github.com/guard/guard#readme
# gem uninstall guard-phpunit && gem install guard-phpunit2

#notification "terminal-notifier-guard"

guard 'phpunit2', :cli => '--colors', :tests_path => 'tests' do
  watch(%r{^.+Test\.php$})

  watch(%r{Sinergia/Ofx/(.+).php}) {|m| "tests/#{m[1]}Test.php"}
end
