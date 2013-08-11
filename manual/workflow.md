Hades Workflow
==============
###stage 1
* A client (or user) requests a page/document. **Hades** identifies *request*.
* with the *request*:
	- [Hermes](https://github.com/sentfanwyaerda/Hermes) starts logging
	- [Morpheus](https://github.com/sentfanwyaerda/Morpheus) starts generating a ``.morph`` file
* The client gets identified by [Heracles](https://github.com/sentfanwyaerda/Heracles), might initiate *authentication*, inflames ``.morph`` file with client specific variables.
* The *request* inflames the ``.morph`` file.

###stage 2
* [Morpheus](https://github.com/sentfanwyaerda/Morpheus) processes the ``.morph`` file and initiates list for compiling.
* gathers all data (including sub-requests) through several methods and filters

###stage 3
* [Morpheus](https://github.com/sentfanwyaerda/Morpheus) compiles ``.morph`` to the requested page/document and saves result as cache combined with (and within) ``.morph``.
* [Hermes](https://github.com/sentfanwyaerda/Hermes) updates log-entry to final version.

