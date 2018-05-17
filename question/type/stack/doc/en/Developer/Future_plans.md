# Future plans

How to report bugs and make suggestions is described on the [community](../About/Community.md) page.

Note, where the feature is listed as "(done)" means we have prototype code in the testing phase.

## Features to add for STACK 4.1 or later ##

### Units Inputs ###

* Convestion from Celsius to Kelvin?  What units to choose for degrees Celsius which don't conflict with Coulomb?
* Support for United States customary units?
* Add an option to validation to require compatible units with the teacher's answer, not just some units.
* Create a mechanism to distinguish between `m/s` and `m*s^-1`, both at validation and answer test levels.
* Create a mechanism to distinguish between `m/s/s` and `m/s^2`, both at validation and answer test levels.
* Add support for testing for error bounds in units.  E.g. `9.81+-0.01m/s^2`.  There is already CAS code for this, and the error bounds are an optional 3rd argument to `stackunits`.  This is currently only used to reject students' answers as invalid.

### Inputs ###

* Add support for coordinates, so students can type in (x,y).  This should be converted internally to a list.
* Add new input types
 1. Dragmath (actually, probably use javascript from NUMBAS instead here, or the MathDox editor).
 2. Sliders.
 3. Geogebra input (protoype already exisits: needs documentation, testing and support).
* It is very useful to be able to embed input elements in equations, and this was working in STACK 2.0. However is it possible with MathJax or other Moodle maths filters?
  This might offer one option:  http://stackoverflow.com/questions/23818478/html-input-field-within-a-mathjax-tex-equation
* In the MCQ input type: Add choose N (correct) from M feature (used at Aalto).
* A new MCQ input type with a "none of these" option which uses Javascript to degrade to an algebraic input: https://community.articulate.com/articles/how-to-use-an-other-please-specify-answer-option
* Add an option for "no functions" which will always insert stars and transform "x(" -> "x*(" even when x occurs as both a function and a variable.
* Make the syntax hint CAS text, to depend on the question variables.
* Make the extra options CAS text as well.

### Improve the editing form ###

* A button to remove a given PRT or input, without having to guess that the way to do it is to delete the placeholders from the question text.
* A button to add a new PRT or input, without having to guess that the way to do it is to add the placeholders to the question text.
* A button to save the current definition and continue editing. This would be a Moodle core change. See https://tracker.moodle.org/browse/MDL-33653.
* Add functionality to add a "warning" to the castext class.  Warnings should not prevent execution of the code but will stop editing.

### Other ideas ###

* Document ways of using JSXGraph  `http://jsxgraph.org` for better support of graphics.
* Better options for automatically generated plots.  (Aalto use of tikzpicture?)  (Draw package?)
* Implement "Banker's rounding" option which applies over a whole question, and for all answer tests.
* Implement "CommaError" checking for CAS strings.  Make comma an option for the decimal separator.
* Implement "BracketError" option for inputs.  This allows the student's answer to have only those types of parentheses which occur in the teacher's answer.  Types are `(`,`[` and `{`.  So, if a teacher's answer doesn't have any `{` then a student's answer with any `{` or `}` will be invalid.
* It would be very useful to have finer control over the validation feedback. For example, if we have a polynomial with answer boxes for the coefficients, then we should be able to echo back "Your last answer was..." with the whole polynomial, not just the numbers.
* Make the mark and penalty fields accept arbitrary maxima statements.
* Decimal separator, both input and output.
* Check CAS/maxima literature on -inf=minf.
* Introduce a variable so the maxima code "knows the attempt number". [Note to self: check how this changes reporting].  This is now being done with the "state" code in the abacus branch.
* See YAML developments: facility to import test-cases in-bulk as CSV (or something). Likewise export.
* Refactor answer tests.
 1. They should be like inputs. We should return an answer test object, not a controller object.
 2. at->get_at_mark() really ought to be at->matches(), since that is how it is used.
 3. Use `defstruct` in Maxima for the return objects. (Note to self: `@` is the element access operator).
* Make the PRT Score element CAS text, so that a value calculated in the "Feedback variables" could be included here.
* Refactor the STACK return object in maxima as a structure. ` ? defstruct`.  Note that `@` is the element access operator.
* Refector blocks parser so that evaluation of anything inside a comment block is ignored, this will allow it to contain contents are syntactically incorrect, e.g. mismatched blocks.
*   A STACK maxima function which returns the number of decimal places/significant figures in a variable (useful when providing feedback)

## Features that might be attempted in the future - possible self contained projects ##

* Read other file formats into STACK.  In particular
  * AIM
  * WebWork, including the Open Problem Library:  http://webwork.maa.org/wiki/Open_Problem_Library
  * MapleTA (underway: see https://github.com/maths/moodle-qformat_mapleta)
  * Wiris
* Possible Maxima packages:
 * Better support for rational expressions, in particular really firm up the PartFrac and SingleFrac functions with better support.
 * Support for the "draw" package.
* Add support for qtype_stack in Moodle's lesson module.
* Improve the way questions are deployed.
 1. Auto deploy.  E.g. if the first variable in the question variables is a single a:rand(n), then loop a=0..(n-1).
 2. Remove many versions at once.
* When validating the editing form, also evaluate the Maxima code in the PRTs, using the teacher's model answers.
* You cannot use one PRT node to guard the evaluation of another, for example Node 1 check x = 0, and only if that is false, Node 2 do 1 / x. We need to change how PRTs do CAS evaluation.

### Authoring and execution of PRTs.

Can we write the whole PRT as Maxima code?  This seems like an attractive option, but there are some serious problems which make it probably impratical.

1. Error trapping.  Currently, the arguments each answer test are evaluated with Maxima's `errcatch` command independently before the answer test is executed.  This helps track down the source of any error. If we write a single Maxima command for the PRT (not just one node) then it is likely that error trapping will become much more difficult.
2. Not all answer tests are implemented in pure Maxima!  Answer tests are accessed through this class `moodle-qtype_stack/stack/answertest/controller.class.php` only those which make use of `stack_answertest_general_cas` are pure maxima.  Many of the numerical tests use PHP code to infer the number of significant figures.  While we could (perhaps) rewrite some of these in Maxima, they were written in PHP as it is significantly easier to do so.

So, while it is attractive to ask for the PRT as a single Maxima function it is currently difficult to do so.

The current plan is to produce a solid YAML mark up language for PRTs.

Other (past ideas) were http://zaach.github.com/jison/ or https://github.com/hafriedlander/php-peg.


## Improvements to the "equiv" input type

* Add an option to display and/or using language strings not '\wedge', '\vee'.
* Improve spacing of comments, e.g. \intertext{...}?
* Auto identify what the student has done in a particular step.

Model solutions.

* Follow a "model solution", and give feedback based on the steps used.  E.g. identify where in the students' solution a student deviates from the model solution.
* Develop a metric to measure the distance between expressions.  Use this as a measure of "step size" when working with expressions.

Add mathematical support in the following order.

1. Equating coefficients as a step in reasoning by equivalence. E.g. \( a x^2+b x+c=r x^2+s x+t \leftrightarrow a=r \mbox{ and } b=s \mbox{ and } c=t\). See `poly_equate_coeffs` in assessment.mac
2. Solving simple simultaneous equations.  (Interface)
3. Logarithms and simple logarithmic equations.
4. Include calculus operations.
5. Allow students to create and use functions of their own (currently forbidden).

* Add a "Not equals" operator.  For example:

    infix("<>");
    p:x<>y;
    texput("<>","{\neq}", infix);
    tex(p);


## STACK custom reports

Basic reports now work.

* Really ensure "attempts" list those with meaningful histories.  I.e. if possible filter out navigation to and from the page etc.
* Add better maxima support functions for off-line analysis.
* A fully maxima-based representation of the PRT?
