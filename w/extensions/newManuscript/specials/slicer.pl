#!/usr/bin/perl

##########################################################################################################
# Copyright (C) 2013 Richard Davis
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License Version 2, as
# published by the Free Software Foundation.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
# Unix:
# slicer.pl --input_file /data/incoming/070/070_001_001.jpg --output_path /web/root/images
#
# Windows:
# slicer.pl --input_file D:/data/images/BOX_070/070_001_001.jpg --output_path D:/web/root/images
#
##########################################################################################################

my $usage = "slicer.pl --<full input path> --output_path <full_output_path>";

use File::Basename;
use FindBin '$Bin';
use Cwd 'abs_path';
use Getopt::Long;

require("$Bin/slice.pl");

my $input_file = '';
my $output_path = '';
					  					    
$result = GetOptions (  'input_file=s'  => \$input_file
		      		  , 'output_path=s' => \$output_path );

unless ( -e $input_file )  { die( "Error: The input file $input_file does not exist\n" ); }		      		  
unless ( -d $output_path ) { die( "Error: The output path $output_path does not exist\n" ); }

die "Error: $input_file is not a JPEG or JPG file" unless ($input_file =~ /\.jpg$/ || $input_file =~ /\.jpeg$/);

my $file_full_path = abs_path( $input_file ); 

if($input_file =~ /\.jpg$/){
  $file_full_path =~ s/\.jpg$//;
}elsif($input_file =~ /\.jpeg$/){
  $file_full_path =~ s/\.jpeg$//;
}

my($file_name, $dir, $ext) = fileparse($file_full_path);

$output_path           = $output_path . '/';

print "Creating directory $output_path\n";

mkdir $output_path;

unless ( -d $output_path ) { die( "Error: Failed to create the output path $output_path\n" ); }	

my $tmp_output_path    = $output_path . 'slice';

# e.g. $output_path = '<output_path>/user_name/slice

my $target_output_path = $output_path . $file_name;

# e.g. $output_path = '<output_path>/user_name/file_name

print "Processing $file_full_path\n";                 

$fault = slice( $input_file, $output_path );
warn $fault if $fault;

unless ( -d $tmp_output_path ) { die( "Error: slice failed to create $tmp_output_path\n" ); }

print "Moving $tmp_output_path to $target_output_path\n";

rename(  $tmp_output_path, $target_output_path ) || die ( "Error: Renaming $tmp_output_path to $target_output_path failed\n" );


































