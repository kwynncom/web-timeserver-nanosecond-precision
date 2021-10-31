# web-timeserver-nanosecond-precision
nanosecond precise web timeserver with chrony validation

The inputs /outputs of this code (5 inputs / features):

IN: https://kwynn.com/t/20/10/timeserver/nstimeraw.php
OUT: 1635615645680522574 [nanoseconds in UNIX Epoch]

IN: https://kwynn.com/t/20/10/timeserver/chronyParsed.php
see full output below

IN: https://kwynn.com/t/20/10/timeserver/chronyraw.php
see "chronyc tracking" output below

IN: https://kwynn.com/t/20/10/timeserver/chronyraw.php?opt=sourcestats
210 Number of sources = 1
Name/IP Address            NP  NR  Span  Frequency  Freq Skew  Offset  Std Dev
==============================================================================
169.254.169.123             7   6   56m     -0.000      0.018   -111ns  8156ns

IN: https://kwynn.com/t/20/10/timeserver/chronyraw.php?opt=both
OUT: see further below

**********
EXPLANATION

https://kwynn.com/t/20/10/timeserver/ 

That page explains quite a bit (it is this repo's /index.html).  The following was written months later and is esoteric.

UPDATE: I'm moving the sourcestats parser to another repo.  (2021/10/30)  I'll try to remember to move this later.

My sourcestats parser makes several assumptions / has limitations:

1. I'm assuming a single source
2. I have no idea if "h" for hours will ever happen.  My observation is that it would be rare, so I can't test for it.

sourcestats example (keyword-ssExOut):

$ chronyc sourcestats
210 Number of sources = 1
Name/IP Address            NP  NR  Span  Frequency  Freq Skew  Offset  Std Dev
==============================================================================
169.254.169.123             6   5   86m     -0.007      0.045  -5409ns    20us

my output, with discussion

1. Number of timeserver polls / samples currently used in the timekeeping mathematical model (regression).
2. Span, in seconds, that these polls were taken.  

With a stable model, the span may go over an hour.  Right now I'm seeing 86m (minutes) on kwynn.com, as above.  A few seconds means that chrony has just 
restarted.  

Chrony doesn't show data until 3 points / polls / samples. How high the number gets depends on minpoll and maxpoll settings.   The short answer is that 
a sample of 6 may be as perfect as it gets.  I didn't set minpoll and maxpoll on AWS (kwynn.com) in large part because Amazon's example doesn's show this.  
Amazon's example is 

server 169.254.169.123 prefer iburst
https://aws.amazon.com/blogs/aws/keeping-time-with-amazon-time-sync-service/

I added xleave after iburst, but I'm not sure it does anything.  I think xleave does (sometimes) work between my local system and kwynn.com.  

Minpoll and maxpoll refer to the min and max polling interval.  I don't remember what the default maxpoll is, but kwynn.com is often at a poll 
interval of 1,000 seconds (17 minutes), so that 86m span is 6 samples (86 / 17 = 5 and then + 1 for the 0th point).  At that setting, kwynn.com is 
extremely accurate (with "extreme" perhaps to be defined later).  I am not trying to achieve anything better than kwynn.com.

On my local server, I have the maxpoll interval set to 6 / 2^6s / 64s.  My local machine keeps 64 samples when it's stable, so that that's a span of 63 
minutes (currently showing 66), which is on the order of Kwynn.com.  

    // Root dispersion : 0.031060036 seconds
    // RMS offset      : 0.019947561 seconds
*********************
********************
After waking up a hibernating system, the first call:

$ chronyc tracking
Reference ID    : 32CDF417 (ntp4.doctor.com)
Stratum         : 3
Ref time (UTC)  : Thu Oct 22 02:50:54 2020
System time     : 0.000000000 seconds slow of NTP time
Last offset     : +0.491677701 seconds
RMS offset      : 0.491677701 seconds
Frequency       : 8.102 ppm slow
Residual freq   : +0.049 ppm
Skew            : 0.076 ppm
Root delay      : 0.061227806 seconds
Root dispersion : 0.042751539 seconds
Update interval : 0.0 seconds
Leap status     : Normal

/* Regarding the Amazon (Web Services) Time Sync Service and 169.254.169.123, see
https://aws.amazon.com/blogs/aws/keeping-time-with-amazon-time-sync-service/

$ chronyc tracking
Reference ID    : A9FEA97B (169.254.169.123)
Stratum         : 4

https://chrony.tuxfamily.org/


validating chrony / false positive
If you boot a machine with no network access, you get something like this:

$ chronyc tracking
Reference ID    : 00000000 ()
Stratum         : 0
Ref time (UTC)  : Thu Jan 01 00:00:00 1970
System time     : 0.000000000 seconds slow of NTP time
Last offset     : +0.000000000 seconds
RMS offset      : 0.000000000 seconds
Frequency       : 8.333 ppm slow
Residual freq   : +0.000 ppm
Skew            : 0.000 ppm
Root delay      : 1.000000000 seconds
Root dispersion : 1.000000000 seconds
Update interval : 0.0 seconds
Leap status     : Not synchronised

**************
******************
FULL I/O

IN: https://kwynn.com/t/20/10/timeserver/chronyParsed.php
OUT [I am adding newlines] 
{"first_server_timestamp":{"number":1635615646419222645,"unit_long":"nanoseconds in UNIX Epoch","unit":"ns","number_type":"integer"},
"cmd":"chronyc tracking","basic_array":{"Reference ID":"A9FEA97B (169.254.169.123)","Stratum":"4","Ref time (UTC)":"Sat Oct 30 17:36:44 2021",
"System time":"0.000007654 seconds slow of NTP time","Last offset":"-0.000018202 seconds","RMS offset":"0.000023542 seconds","Frequency":"65.596 ppm fast",
"Residual freq":"-0.000 ppm","Skew":"0.022 ppm","Root delay":"0.000321157 seconds","Root dispersion":"0.000485133 seconds",
"Update interval":"1039.0 seconds","Leap status":"Normal"},"raw_cmd_result":
"Reference ID    : A9FEA97B (169.254.169.123)\nStratum         : 4\nRef time (UTC)  : Sat Oct 30 17:36:44 2021\n
System time     : 0.000007654 seconds slow of NTP time\nLast offset     : -0.000018202 seconds\n
...


IN: https://kwynn.com/t/20/10/timeserver/chronyraw.php?opt=both
OUT: 
Reference ID    : A9FEA97B (169.254.169.123)
Stratum         : 4
Ref time (UTC)  : Sat Oct 30 17:36:44 2021
System time     : 0.000007578 seconds slow of NTP time
Last offset     : -0.000018202 seconds
RMS offset      : 0.000023542 seconds
Frequency       : 65.596 ppm fast
Residual freq   : -0.000 ppm
Skew            : 0.022 ppm
Root delay      : 0.000321157 seconds
Root dispersion : 0.000486915 seconds
Update interval : 1039.0 seconds
Leap status     : Normal

****** sourcestats ******
210 Number of sources = 1
Name/IP Address            NP  NR  Span  Frequency  Freq Skew  Offset  Std Dev
==============================================================================
169.254.169.123             7   6   56m     -0.000      0.018   -112ns  8156ns
