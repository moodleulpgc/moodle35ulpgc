# Multiple choice questions

The whole point of STACK is not to use multiple choice questions, but instead to have the student enter an algebraic expression!  That said their are occasions where it is very useful, if not necessary, to use multiple choice questions in their various forms.  STACK's use of a CAS is then very helpful to generate random versions of multiple choice questions based on the mathematical values.

This can also be one input in a multi-part randomly generated question. E.g. you might say "which method do you need to integrate \( \sin(x)\cos(x) \)?" and give students the choice of (i) trig functions first, (ii) parts, (iii) substitution, (iv) replace with complex exponentials.  (Yes, this is a joke: all these methods can be made to work here!)  Another algebraic input can then be used for the answer.

Please read the section on [inputs](Inputs.md) first.  If you are new to STACK please note that in STACK MCQs are *not* the place to start learning how to author questions.  Please look at the [authoring quick-start guide](Authoring_quick_start.md).  Multiple choice input types return a CAS object which is then assessed by the potential response tree.  For this reason, these inputs do not provide "feedback" fields for each possible answer, as does the Moodle multiple choice input type.

The goal of these input types is to provide *modest* facilities for MCQ.  An early design decision was to restrict each of the possible answers to be a CAS expression.  In particular, we decided *NOT* to make each possible answer [castext](CASText.md).  Adopting castext would have provided more flexibility but would have significantly increased the complexity of the internal code. If these features are extensively used we will consider modifying the functionality.  Please contact the developers with comments.

## Model answer ##

This input type uses the "model answer" both to input the teacher's answer and the other options. In this respect, this input type is unique, and the "model answer" field does *not* contain just the teacher's model answer.  Constructing a correctly formed model answer is complex, and so this input type should be considered "advanced".  New users are advised to gain confidence writing questions with algebraic inputs first, and gain experience in using Maxima lists.

The "model answer" must be supplied in a particular form as a list of lists `[[value, correct(, display)], ... ]`.

* `value` is the value of the teacher's answer
* `correct` must be either `true` or `false`.  If it is not `true` then it will be considered to be `false`!
* (optional) `display` is another CAS expression to be displayed in place of `value`.  Be cautious!  This can be a string value here, but it will be passed through the CAS if you choose the LaTeX display option below.  `display` is only used in constructing the question.  The STACK will take `value` as the student's answer internally, regardless of what is set here.

For example

     ta:[[diff(p,x),true],[p,false],[int(p,x),false]]

At least one of the choices must be considered `correct`.  However, the `true` and `false` values are only used to construct the "teacher's correct answer".  You must still use a [potential response tree](Potential_response_trees.md) to assess the student's answer as normal.

STACK provides some helper functions

1. `mcq_correct(ta)` takes the "model answer" list and returns a list of values for which `correct` is true.
2. `mcq_incorrect(ta)` takes the "model answer" list and returns a list of values for which `correct` is false.

Note, that the optional `display` field is *only* used when constructing the choices seen by the student when displaying the question.  The student's answer will be the `value`, and this value is normally displayed to the student using the validation feedback, i.e. "Your last answer was interpreted as...".  A fundamental design principal of STACK is that the student's answer should be a mathematical expression, and this input type is no exception.  In situations where there is a significant difference between the optional `display` and the `value` which would be confusing, the only current option is to turn off validation feedback.  After all, this should not be needed anyway with this input type.  In the example above when a student is asked to choose the right method the `value` could be an integer and the display is some kind of string.  In this example the validation feedback would be confusing, since an integer (which might be shuffled) has no correspondence to the choices selected.  *This behaviour is a design decision and not a bug! It may change in the future if there is sufficient demand, but it requires a significant change in STACK's internals to have parallel "real answer" and "indicated answer".  Such a change might have other unintended and confusing consequences.*

Normally we don't permit duplicate values in the values of the teacher's answer.  If the input type receives duplicate values STACK will throw an error.  This probably arises from poor randomisation.  However it may be needed.  If duplicate entries are permitted use the display option to create unique value keys with the same display. *This behaviour is a design decision may change in the future.*

When STACK displays the "teacher's answer", e.g. after a quiz is due, this will be constructed from the `display` fields corresponding to those elements for which `correct` is `true`.  I.e. the "teacher's answer" will be a list of things which the student could actually select.  Whether the student is able to select more than one, or if more than one is actually included, is not checked.   The teacher must indicate at least one choice as `true`.  

If you need "none of these" you must include this as an explicit option, and not rely on the student not checking any boxes in the checkbox type.  Indeed, it would be impossible to distinguish the active selection of "none of these" from a passive failure to respond to the question.

If one of the responses is \(x=1 \mbox{ or } x=2\) then it is probably best to use `nounor` which is commutative and associative.  Do not use `or` which always simplifies its arguments.  In this example `x=1 or x=2` evaluates to `false`.

## Internals ##

The dropdown and radio inputs return the `value`, but the checkbox type returns the student's answer as Maxima list, even if they have only chosen one option.

If, when authoring a question, you switch from radio/dropdown to checkboxes or back, you will probably break a PRT because of mismatched types.

For the select and radio types the first option on the list will always be "Not answered".  This enables a student to retract an answer and return a "blank" response.

For the checkbox type there is a fundamental ambiguity between a blank response and actively not selecting any of the provided choices, which indicates "none of the others".  Internally STACK has a number of "states" for a student's answer, including `BLANK`, `VALID`, `INVALID`, `SCORE` etc.  A student who has not answered will be considered `BLANK`. This is not invalid, and potential response trees which rely on this input type will not activate.  To enable a student to indicate "none of the others", the teacher must add this as an explicit option.  Note, this will not return an empty list as the answer as might be expected: it will be the `value` of that selection and you could give this option the value of `null`, for example, which is a Maxima atom.  For the radio and dropdown types STACK always adds a "not answered" option as the first option.  This allows a student to retract their choice, otherwise they will be unable to "uncheck" a radio button, which will be stored, validated and possibly assessed (to their potential detriment).

We did not add support for a special internal "none of the others" because the teacher still needs to indicate whether this is the true or false answer to the question.  To support randomisation, this needs to be done as an option in the teacher's answer list.

## Extra options ##

These input types make use of the "Extra options" field of the input type to pass in options.  These options are not case sensitive.  This must be a comma separated list of values as follows, but currently the only option is to control the display of mathematical expressions.

The way the items are displayed can be controlled by the following options.

* `LaTeX` The default option is to use LaTeX to display the options, using an inline maths environment `\(...\)`.  This is probably better for radio and checkboxes.  It sometimes works in dropdowns, but not always and we need to test this in a wider variety of browsers.
* `LaTeXdisplay` use LaTeX to display the options, using the display maths environment `\[...\]`.
* `LaTeXinline` use LaTeX to display the options, using the inline maths environment `\(...\)`.
* `LaTeXdisplaystyle` use LaTeX to display the options, using the inline maths environment and the displaystyle option `\(\displaystyle...\)`.
* `casstring` does not use the LaTeX value, but just prints the casstring value in `<code>...</code>` tags.
* `nonotanswered` removes the ``Not answered'' option from radio and dropdown.  This is _not recommended_ as it means a student has no opportunity to "uncheck" a radio button once selected.  They may wish not to answer, rather than save an incorrect answer.

## Randomly shuffling the options ##

To randomly shuffle the options create the list in the question variables and use the Maxima command `random_permutation` in the question variables.

For example, the question variables might look like the following.

    /* Create a list of potential answers. */
    p:sin(2*x);
    ta:[[diff(p,x),true],[p,false],[int(p,x),false],[cos(2*x)+c,false]];
    /* The actual correct answer.    */
    tac:diff(p,x)
    /* Randomly shuffle the list "ta". */
    ta:random_permutation(ta);
    /* Add in a "None of these" to the end of the list.  The Maxima value is the atom null. */
    tao:[null, false, "None of these"];
    ta:append(ta,[tao]);

These command ensure (1) the substantive options are in a random order, and (2) that the `None of these` always comes at the end of the list. Note, the value for the `None of these` is the CAS atom `null`.  In Maxima `null` has no special significance but it is a useful atom to use in this situation.

As the Question Note, you might like to consider just takeing the first item from each list, for example:

    {@maplist(first,ta)@}.  The correct answer is {@tac@}.

This note stores both the correct answer and the order shown to the student without the clutter of the `true/false` values or the optional display strings.  Remember, random versions of a question are considered to be the same if and only if the question note is the same, so the random order must be part of the question note if you shuffle the options.

## Constructing MCQ arrays in Maxima ##

It is not easy to construct MCQ arrays in Maxima.  This section contains some tips for creating them, using Maxima's `lambda` command.  Below is an example of a correctly constructed teacher's answer.

    ta:[[x^2-1,true],[x^2+1,false],[(x-1)*(x+1),true],[(x-i)*(x+i),false]]

To create a list of correct answers you could use the function `mcq_correct(ta)`.  This essentially consists of the following code.

    maplist(first, sublist(ta, lambda([ex], second(ex))));

The function `sublist` "filters" out those entries of `ta` for which the second element of the list is true.  We then "map" first onto these entries to pull out the value.  It is relatively simple to modify this code to extract the incorrect entries, the displayed forms of the correct entries etc.

To go in the other direction, the first list `ta1` is considered "correct" and the second `ta2` is considered incorrect.

    ta1:[x^2-1,(x-1)*(x+1)];
    ta1:maplist(lambda([ex],[ex, true]), ta1);
    ta2:[x^2+1,(x-i)*(x+i)];
    ta2:maplist(lambda([ex],[ex, false]), ta2);
    ta:append(ta1,ta2);
    /* If you want to shuffle the responses then use the next line. */
    ta:random_permutation(ta);

Also, you can use STACK's `rand_selection(L, n)` to select \(n\) different elements from the list \(L\).  Say you have the following list of wrong answers and you want to take only 3 out of 5.

    ta2:[x^2,w^2,w^6,z^4,2*z^4];
    ta2:rand_selection(ta2, 3);
    /* Then, as before. */
    ta2:maplist(lambda([ex],[ex, false]), ta2);


Another way to create a MCQ answer list is to have Maxima decide which of the answers are true.  For example, in this question the student has to choose which of the answers are integers.

    L:[1,4/2,3.0,2.7,1/4,%pi,10028];
    /* Map the appropriate predicate to the list to create the true/false list. */
    A:maplist(integerp,L);
    /* Note the use of zip_with together with the list constructing function "[". */
    ta:zip_with("[",L,A);
    /* If you want to shuffle the responses then use the next line. */
    ta:random_permutation(ta);

## MCQ helper functions ##

STACK has two helper functions to create MCQ arrays in Maxima.

    multiselqn(corbase, numcor, wrongbase, numwrong)

This function takes two lists `corbase` and `wrongbase` and two integers `numcor` and `numwrong`.  It randomly selects `numcor` from `corbase`, and `numwrong` from `wrongbase` and then creates the MCQ list with these selections, and an answernote.

The function returns a list with two arguments.  The first argument of the list is the MCQ array, the second is just the list of answers which is useful for the answer note.  Note, this function does use `random_permutation` internally to randomly order the random selections.

For example, the following generates random expressions for an MCQ calculus question.  Note the use of `ev(...)` later to evaluate the derivative.

    trg:rand([sin(p), cos(p)]);
    dtrg:diff(trg, p);
    wrongbase:[a*trg, 2*a*x*trg, -2*a*x*trg, ev(dtrg, p=2*a*x), 2*a*x*ev(dtrg, p=2*a*x)];
    p:a*x^2+b;
    wrongbase:ev(wrongbase); /* Now we have a value for p, the extra evaluation will use it. */
    ans:diff(ev(trg), x)$
    multisel:multiselqn([ans], 1, wrongbase, 3);
    ta1:multisel[1];
    version:multisel[2];

In the above example there is only one correct answer, so we just select `1` from `[ans]`.  This is fine, and we then choose three randomly generated wrong answers.

This returns (for example) the values

    ta1 = [[-2*a*x*cos(a*x^2+b),false],[-sin(2*a*x),false],[a*cos(a*x^2+b),false],[-2*a*x*sin(a*x^2+b),true]];
    version = [-2*a*x*cos(a*x^2+b),-sin(2*a*x),a*cos(a*x^2+b),-2*a*x*sin(a*x^2+b)];

The following function does a similar job when we have MCQ display strings.

    multiselqndisplay(corbase, numcor, wrongbase, numwrong)

For example, here the return values could be

    ta1 = [[3,false,2*a*x*sin(a*x^2+b)],[2,false,a*sin(a*x^2+b)],[5,false,cos(2*a*x)],[1,true,2*a*x*cos(a*x^2+b)]]
    version = [3,2,5,1]

The function `multiselqndisplay` automatically assigns numbers \(1,\cdots, k\) to the `corbase` entries, and then \(k+1,\cdots, n\) to the `wrongbase` entries so that the numbers returned by the input type uniqely map to the entries in the two lists regardless of which random version is generated.


## Dealing with strings in MCQ ##

A likely situation is that a teacher wants to include a language string as one of the options for a student's answer in a multiple choice question.

Recall: *A fundamental design principal of STACK is that the student's answer should be a mathematical expression which can be manipulated by the CAS as a valid expression.* Students are very limited in the keywords they are permitted to use in an input type.  It is very likely that strings will contain keywords forbidden in student expressions.

One option to overcome this is to do something like this as one option in the teacher's response:

    [C, false, "(C) None of the other options"]

The optional display part of this input is displayed to the student.  Their answer is the (valid) CAS atom `C` which the PRT will deal with appropriately.  This work-around is unlikely to sit well with the `shuffle` option.  As we said, the current goal is to only provide modest MCQ facilities.

The quotation marks will be removed from strings, and the strings will not be wrapped `<code>...</code>` tags or LaTeX mathematics environments.

Question authors should consider using the Moodle MCQ question type in addition to these facilities for purely text based answers.

## Dealing with plots in MCQ ##

It is possible to use plots as the options in a STACK MCQ.  

Recall again the MCQ are limited to legitimate CAS objects.  The `plot` command returns a string which is the URL of the dyanamically generated image on the server.  The "value" of this can't be assessed by the potential response trees.  For this reason you must use the display option with plots and must only put the plot command in the display option. (Otherwise STACK will throw an error: this behaviour could be improved).  For example, to create a correct answer consiting of three plots consider the following in the question variables.

    p1:plot(x,[x,-2,2],[y,-3,3])
    p2:plot(x^2,[x,-2,2],[y,-3,3])
    p3:plot(x^3,[x,-2,2],[y,-3,3])
    ta:[[1,true,p1],[2,false,p2],[3,false,p3]]

The actual CAS value of the answer returned will be the respective integer selected (radio or dropdown) or list of integers (checkbox).  The PRT can then be used to check the value of the integer (or list) as normal.  

For this reason you will probably want to switch off the validation feedback ``your last answer was...".  

Using a PRT is slight overkill, but it maintains the consistent internal design.

## Dealing with external images in MCQ ##

It is also possible to embed the URL of an externally hosted image as the "display" field of an MCQ.  The string is not checked, and is also passed through the CAS.  This feature is fragile to being rejected as an invalid CAS object, and so is not recommended.  (This could also be improved...)

For example, the question variables could be something like

    i1:"<img src='http://www.maths.ed.ac.uk/~csangwin/Pics/z1.jpg' />"
    i2:"<img src='http://www.maths.ed.ac.uk/~csangwin/Pics/z2.jpg' />"
    i3:"<img src='http://www.maths.ed.ac.uk/~csangwin/Pics/z3.jpg' />"
    ta:[[1,true,i1],[2,false,i2],[3,false,i3]]


## Writing question tests ##

Quality control of questions is important.  See the notes on [testing](Testing.md) questions.  

When entering test cases the question author must type in the CAS expression they expect to be the `value` of the student's answer (NOT the optional `display` field!).  For example, if the teacher's answer (to a checkbox) question is the following.

     ta:[[x^2-1,true],[x^2+1,false],[(x-1)*(x+1),true],[(x-i)*(x+i),false]]

Then the following test case contains all the "true" answers.

     [x^2-1,(x-1)*(x+1)]

There is currently minimal checking that the string entered by the teacher corresponds to a valid choice in the input type.  If your test case returns a blank result this is probably the problem.     
