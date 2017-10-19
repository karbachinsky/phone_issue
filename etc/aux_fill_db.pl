#!/usr/bin/env perl

# Fills database with different fake phones

use warnings;
use strict;


for my $i (5..100000) {
   my $number = join("", map {int(rand()*10) } 1..10);

   print "insert into phone values ($i, \"$number\", 1);\n";
}
