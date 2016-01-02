project(movim)
cmake_minimum_required(VERSION 2.8)

exec_program("cat ../VERSION | cut -d . -f 1 2>/dev/null"
        OUTPUT_VARIABLE MOVIM_VERSION_MAJOR)
exec_program("cat ../VERSION | cut -d . -f 2 2>/dev/null"
        OUTPUT_VARIABLE MOVIM_VERSION_MINOR)
exec_program("git rev-parse --short HEAD 2>/dev/null"
        OUTPUT_VARIABLE MOVIM_VERSION_PATCH)

set(VERSION_MAJOR "${MOVIM_VERSION_MAJOR}")
set(VERSION_MINOR "${MOVIM_VERSION_MINOR}")
set(VERSION_PATCH "${MOVIM_VERSION_PATCH}")

#install(DIRECTORY "config"     DESTINATION "/etc/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "debian/etc" DESTINATION "/")
install(DIRECTORY "debian/lib" DESTINATION "/")
install(DIRECTORY "app"        DESTINATION "/usr/share/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "src"        DESTINATION "/var/lib/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "system"     DESTINATION "/usr/share/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "lib"        DESTINATION "/var/lib/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "locales"    DESTINATION "/var/lib/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "themes"     DESTINATION "/var/lib/${CMAKE_PROJECT_NAME}")
install(DIRECTORY "vendor"     DESTINATION "/var/lib/${CMAKE_PROJECT_NAME}" REGEX ".git" EXCLUDE)

install(FILES index.php bootstrap.php daemon.php linker.php mud.php composer.json "VERSION" DESTINATION "/usr/share/${CMAKE_PROJECT_NAME}")
install(FILES "config/db.example.inc.php" DESTINATION "/etc/${CMAKE_PROJECT_NAME}")

# package settings
set(CPACK_PACKAGE_DESCRIPTION_SUMMARY "Kick Ass Social Network, decentralized and fully based on XMPP ")
set(CPACK_PACKAGE_VENDOR "Jaussoin Timothée")
set(CPACK_PACKAGE_CONTACT "edhelas@movim.eu")
set(CPACK_PACKAGE_VERSION_MAJOR "${VERSION_MAJOR}")
set(CPACK_PACKAGE_VERSION_MINOR "${VERSION_MINOR}")
set(CPACK_PACKAGE_VERSION_PATCH "${VERSION_PATCH}")
set(CPACK_RESOURCE_FILE_LICENSE "../COPYING")
set(CPACK_SOURCE_PACKAGE_FILE_NAME "${CMAKE_PROJECT_NAME}_${VERSION}")
set(ACK_PACKAGE_FILE_NAME "${CMAKE_PROJECT_NAME}_${VERSION}")
set(CPACK_DEBIAN_PACKAGE_DESCRIPTION "Kickass distributed social networking platform that protect your privacy an comes with a set of awesome features.")
set(CPACK_DEBIAN_PACKAGE_DEPENDS "php5 (>= 5.4.0), php5-gd (>= 5.4.0), php5-curl (>= 5.4.0), php5-imagick (>= 3.0.0), dbconfig-common(>= 1.8.0)")
set(CPACK_DEBIAN_PACKAGE_MAINTAINER "Jaussoin Timothée <edhelas@movim.eu>")
set(CPACK_DEBIAN_PACKAGE_HOMEPAGE "https://movim.eu/")

SET(CPACK_DEBIAN_PACKAGE_CONTROL_EXTRA "${CMAKE_CURRENT_SOURCE_DIR}/debian/conffiles;${CMAKE_CURRENT_SOURCE_DIR}/debian/config;${CMAKE_CURRENT_SOURCE_DIR}/debian/postinst;${CMAKE_CURRENT_SOURCE_DIR}/debian/postrm;${CMAKE_CURRENT_SOURCE_DIR}/debian/templates;")

set(PACK "DEB" CACHE STRING "Generate a Package")
set(CPACK_GENERATOR ${PACK})

include(CPack)
