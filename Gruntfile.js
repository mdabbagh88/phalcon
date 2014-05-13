module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		project: {
			"app" : ".",
			"name": "cloud9living",
			"assets": "<%= project.app %>/public/assets",
			"dist":"<%= project.assets %>/dist",
			"src":"<%= project.assets %>/src",
			"requirejs": {
				duplicate_excludes: ["jquery", "backbone", "text", "underscore", "knockout", "knockback"],
				individual_modules: ["homepage", "catalog", "sales", "customer", "card", "booking", "views"]
			}
		},
		watch: {
			compass: {
		        files: '<%= project.scss %>',
		        tasks: ['compass:dev', 'concat:css'],
		        options: {
		            spawn: false,
		            livereload:true
		        }
		    }
		},
		sass: {
			dev: {
				options: {
					style: "expanded",
					banner: '<%= tag.banner %>',
				    compass: true
				},
				files: {
					src: "<%= project.src %>/styles/*/*/*.scss",
					dest: "<%= project.src %>/styles/"
				}
			}
		}
	});
	
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-requirejs');
	grunt.registerTask('default', [ 'compass:dev','concat:css','watch' ]);
	grunt.registerTask('production', ['compass:production','concat:css']);
}
