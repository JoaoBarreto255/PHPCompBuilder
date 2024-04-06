## do nothing
help:
	@echo "Uso: make [target]"
	@echo ""
	@echo "Possible targets:"
	@echo "\thelp:\t\t- Display this usage prompt."
	@echo "\ttest:\t\t- Run phpunit battery tests."
	@echo "\tstyle-fix:\t- Fix php coding style unsing php-cs-fixer."

## run phpunit tests.
test:
	@echo "Running phpunit ..."
	composer run-script test;

style-fix:
	@echo "Running cs-fixer ..."
	composer run-script fix-style;